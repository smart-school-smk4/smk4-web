<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\BellHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class MqttService
{
    protected MqttClient $client;
    protected array $config;
    protected bool $isConnected = false;
    protected int $reconnectAttempts = 0;
    protected bool $connectionLock = false;
    protected array $subscriptions = [];
    protected const MAX_RECONNECT_ATTEMPTS = 5;
    protected const RECONNECT_DELAY = 5;

    // Constants for announcement topics
    protected const TOPIC_ANNOUNCEMENT_CONTROL = 'control/relay';
    protected const TOPIC_ANNOUNCEMENT_TTS = 'tts/play';
    protected const TOPIC_ANNOUNCEMENT_STATUS = 'announcement/status';
    protected const TOPIC_ANNOUNCEMENT_RESPONSE = 'announcement/response';

    public function __construct()
    {
        $this->config = config('mqtt');
        // Lazy connection - don't block on constructor
        // Connection will be established when needed
    }

    protected function initializeConnection(): void
    {
        if ($this->isConnected) {
            return; // Already initialized
        }

        try {
            $connectionConfig = $this->getConnectionConfig();
            $this->client = new MqttClient(
                $connectionConfig['host'],
                $connectionConfig['port'],
                $this->generateClientId($connectionConfig)
            );

            if ($this->connect()) {
                $this->subscribeToBellTopics();
                $this->subscribeToAnnouncementTopics();
                $this->subscribeToRmsTopics();
            }
        } catch (\Exception $e) {
            Log::warning('MQTT Initialization failed (will retry later): ' . $e->getMessage());
            // Don't block - let it retry on next publish/subscribe call
        }
    }

    // In MqttService
    public function getConnectionStatus(): array
    {
        return [
            'connected' => $this->isConnected,
            'last_attempt' => Cache::get('mqtt_last_attempt'),
            'queued_messages' => count(Cache::get('mqtt_message_queue', []))
        ];
    }

    protected function getConnectionConfig(): array
    {
        return $this->config['connections'][$this->config['default_connection']];
    }

    protected function generateClientId(array $config): string
    {
        return $config['client_id'] . '_' . uniqid();
    }

    protected function subscribeToBellTopics(): void
    {
        $topics = [
            'bell_schedule' => fn($t, $m) => $this->handleBellNotification($m, 'bell_schedule'),
            'bell_manual' => fn($t, $m) => $this->handleBellNotification($m, 'bell_manual')
        ];

        foreach ($topics as $type => $callback) {
            $this->subscribe($this->config['topics']['events'][$type], $callback);
        }
    }

    /**
     * Subscribe to announcement-related topics
     */
    protected function subscribeToAnnouncementTopics(): void
    {
        $topics = [
            self::TOPIC_ANNOUNCEMENT_STATUS => fn($t, $m) => $this->handleAnnouncementStatus($m),
            self::TOPIC_ANNOUNCEMENT_RESPONSE => fn($t, $m) => $this->handleAnnouncementResponse($m)
        ];

        foreach ($topics as $topic => $callback) {
            $this->subscribe($topic, $callback);
        }
    }

    /**
     * Subscribe to RMS statistics topics
     */
    protected function subscribeToRmsTopics(): void
    {
        $this->subscribe('smk4/rms/statistics', fn($t, $m) => $this->handleRmsStatistics($m));
    }

    /**
     * Handle RMS task statistics from ESP32
     */
    protected function handleRmsStatistics(string $message): void
    {
        try {
            $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
            
            // Validate required fields
            $validator = Validator::make($data, [
                'timestamp' => 'required|integer',
                'total_utilization' => 'required|numeric',
                'schedulable' => 'required|boolean',
                'free_heap' => 'required|integer',
                'tasks' => 'required|array'
            ]);

            if ($validator->fails()) {
                Log::warning('Invalid RMS statistics data', ['errors' => $validator->errors()]);
                return;
            }

            // Store in database
            \App\Models\TaskStatistic::create([
                'boot_timestamp' => $data['timestamp'],
                'total_utilization' => $data['total_utilization'],
                'rms_bound' => $data['rms_bound'] ?? 75.6,
                'schedulable' => $data['schedulable'],
                'free_heap' => $data['free_heap'],
                'tasks' => $data['tasks']
            ]);

            // Store latest in cache for dashboard
            Cache::put('rms_latest_stats', $data, 120);

            Log::info('RMS statistics stored', [
                'utilization' => $data['total_utilization'],
                'schedulable' => $data['schedulable'],
                'tasks_count' => count($data['tasks'])
            ]);

        } catch (\JsonException $e) {
            Log::error('Failed to parse RMS statistics JSON: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('Failed to handle RMS statistics: ' . $e->getMessage());
        }
    }

    /**
     * Handle announcement status updates from devices
     */
    protected function handleAnnouncementStatus(string $message): void
    {
        try {
            $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
            
            Log::info('Announcement status update', [
                'status' => $data['status'] ?? 'unknown',
                'ruang' => $data['ruang'] ?? null,
                'mode' => $data['mode'] ?? null,
                'timestamp' => $data['timestamp'] ?? null
            ]);

            // Store status in cache for quick access
            if (isset($data['ruang'])) {
                Cache::put('announcement_status_'.$data['ruang'], $data['status'], 60);
            }

        } catch (\Exception $e) {
            Log::error('Failed to process announcement status', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    /**
     * Handle announcement responses from devices
     */
    protected function handleAnnouncementResponse(string $message): void
    {
        try {
            $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
            
            Log::debug('Announcement response received', [
                'type' => $data['type'] ?? 'unknown',
                'status' => $data['status'] ?? null,
                'ruang' => $data['ruang'] ?? null,
                'message' => $data['message'] ?? null
            ]);

            // TODO: Add any specific response handling logic here

        } catch (\Exception $e) {
            Log::error('Failed to process announcement response', [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        }
    }

    public function sendRelayControl(string $action, array $ruangans, string $mode = 'manual'): bool
    {
        // Normalisasi target ruangan menjadi indeks numerik (0-based)
        $targets = $this->normalizeRoomTargets($ruangans);

        if (empty($targets)) {
            Log::error('Relay control failed: no valid room targets resolved', [
                'input' => $ruangans
            ]);
            return false;
        }

        $payload = [
            'action' => $action, // 'activate' atau 'deactivate'
            'ruang' => $targets,
            'mode' => $mode,
            'timestamp' => now()->toDateTimeString()
        ];

        try {
            return $this->publish(self::TOPIC_ANNOUNCEMENT_CONTROL, json_encode($payload));
        } catch (\Exception $e) {
            Log::error('Failed to send relay control', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Mengirim pengumuman TTS
     */
    public function sendTTSAnnouncement(array $ruangans, string $message, string $language = 'id-id'): bool
    {
        // Normalisasi target ruangan menjadi indeks numerik (0-based)
        $targets = $this->normalizeRoomTargets($ruangans);

        if (empty($targets)) {
            Log::error('TTS announcement failed: no valid room targets resolved', [
                'input' => $ruangans
            ]);
            return false;
        }

        $payload = [
            'ruang' => $targets,
            'teks' => $message,
            'hl' => $language,
            'timestamp' => now()->toDateTimeString()
        ];

        try {
            return $this->publish(self::TOPIC_ANNOUNCEMENT_TTS, json_encode($payload));
        } catch (\Exception $e) {
            Log::error('Failed to send TTS announcement', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);
            return false;
        }
    }

    /**
     * Fungsi kompatibilitas backward (bisa dihapus setelah update controller)
     * @deprecated
     */
    public function sendAnnouncement(string $mode, array $ruangans, ?string $message = null): bool
    {
        if ($mode === 'tts' && $message) {
            return $this->sendTTSAnnouncement($ruangans, $message);
        }
        
        // Default action untuk mode manual
        return $this->sendRelayControl('activate', $ruangans, $mode);
    }


    /**
     * Get current announcement status for rooms
     */
    public function getAnnouncementStatus(array $ruanganIds): array
    {
        $statuses = [];
        
        foreach ($ruanganIds as $id) {
            $statuses[$id] = Cache::get('announcement_status_'.$id, 'unknown');
        }

        return $statuses;
    }

    /**
     * Normalize room identifiers (names or indices) to numeric relay indices (0-based).
     * Accepts inputs like:
     * - 0, 1, 17, 48 (int or numeric string)
     * - "01 LAB DKV" (prefix digits)
     * - "Kelas X Akutansi1" (any digits in the name)
     * Returns only indices within [0..63].
     */
    private function normalizeRoomTargets(array $ruangans): array
    {
        $targets = [];

        foreach ($ruangans as $room) {
            // Already numeric (int or numeric string)
            if (is_int($room) || (is_string($room) && ctype_digit($room))) {
                $idx = (int) $room;
            } else if (is_string($room)) {
                $idx = null;
                // Prefer leading number as index (e.g., "01 LAB DKV")
                if (preg_match('/^\s*(\d{1,2})/', $room, $m)) {
                    $idx = (int) $m[1];
                } else if (preg_match('/(\d{1,2})/', $room, $m)) {
                    // Fallback: any number present (e.g., "XII DKV2")
                    $idx = (int) $m[1];
                }
                if ($idx === null) {
                    Log::warning('Unable to resolve room index from name', ['room' => $room]);
                    continue;
                }
            } else {
                Log::warning('Unsupported room identifier type', ['room' => $room]);
                continue;
            }

            // Bound check to 0..63 (supports up to 4x PCF8575)
            if ($idx < 0 || $idx > 63) {
                Log::warning('Room index out of bounds (0..63 required)', ['index' => $idx, 'room' => $room]);
                continue;
            }

            $targets[] = $idx;
        }

        // Remove duplicates and reindex
        $targets = array_values(array_unique($targets));
        return $targets;
    }

    protected function handleBellNotification(string $message, string $triggerType): void
    {
        Log::debug("Processing {$triggerType} bell event", compact('message', 'triggerType'));

        try {
            $data = $this->validateBellData($message, $triggerType);
            $history = $this->createBellHistory($data, $triggerType);
            
            $this->logBellEvent($history, $triggerType);

            Log::debug('Raw MQTT payload', [
                'message' => $message,
                'trigger_type' => $triggerType
            ]);
        } catch (\JsonException $e) {
            Log::error("Invalid JSON format in bell notification", [
                'error' => $e->getMessage(),
                'message' => $message
            ]);
        } catch (\InvalidArgumentException $e) {
            Log::error("Validation failed for bell event", [
                'error' => $e->getMessage(),
                'data' => $data ?? null
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to process bell notification", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'trigger_type' => $triggerType
            ]);
        }
    }

    protected function validateBellData(string $message, string $triggerType): array
    {
        $data = json_decode($message, true, 512, JSON_THROW_ON_ERROR);
        
        $rules = [
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'waktu' => 'required|date_format:H:i:s',
            'file_number' => 'required|string|size:4|regex:/^[0-9]{4}$/',
            'volume' => 'sometimes|integer|min:0|max:30',
            'repeat' => 'sometimes|integer|min:1|max:5'
        ];

        $validator = Validator::make($data, $rules);

        if ($validator->fails()) {
            throw new \InvalidArgumentException(
                'Invalid bell data: ' . $validator->errors()->first()
            );
        }

        return $data;
    }

    protected function createBellHistory(array $data, string $triggerType): BellHistory
    {
        return BellHistory::create([
            'hari' => $data['hari'],
            'waktu' => $this->normalizeTime($data['waktu']),
            'file_number' => $data['file_number'],
            'trigger_type' => $triggerType === 'bell_schedule' ? 'schedule' : 'manual',
            'volume' => $data['volume'] ?? 15,
            'repeat' => $data['repeat'] ?? 1,
            'ring_time' => now()
        ]);
    }

    protected function logBellEvent(BellHistory $history, string $triggerType): void
    {
        Log::info("Bell event saved successfully", [
            'id' => $history->id,
            'type' => $triggerType,
            'hari' => $history->hari,
            'waktu' => $history->waktu,
            'file' => $history->file_number
        ]);
    }

    // protected function dispatchBellEvent(BellHistory $history): void
    // {
    //     event(new BellRingEvent([
    //         'id' => $history->id,
    //         'hari' => $history->hari,
    //         'waktu' => $history->waktu,
    //         'file_number' => $history->file_number,
    //         'trigger_type' => $history->trigger_type,
    //         'ring_time' => $history->ring_time->toDateTimeString()
    //     ]));
    // }

    private function normalizeTime(string $time): string
    {
        try {
            return Carbon::createFromFormat('H:i:s', $time)->format('H:i:s');
        } catch (\Exception $e) {
            $parts = explode(':', $time);
            return sprintf("%02d:%02d:%02d", $parts[0] ?? 0, $parts[1] ?? 0, $parts[2] ?? 0);
        }
    }

    public function connect(): bool
    {
        if ($this->connectionLock) {
            Log::debug('MQTT Connection already in progress');
            return false;
        }

        $this->connectionLock = true;

        try {
            if ($this->isConnected) {
                $this->connectionLock = false;
                return true;
            }

            // Check if we should delay reconnect
            $lastFailed = Cache::get('mqtt_last_failed');
            if ($lastFailed && (time() - $lastFailed) < 10) {
                $this->connectionLock = false;
                return false; // Don't spam reconnect
            }

            $connectionSettings = $this->createConnectionSettings();
            $this->client->connect($connectionSettings, true);
            
            $this->handleSuccessfulConnection();
            Cache::forget('mqtt_last_failed');
            return true;
        } catch (\Exception $e) {
            Cache::put('mqtt_last_failed', time(), 60);
            $this->handleConnectionFailure($e);
            return false;
        } finally {
            $this->connectionLock = false;
        }
    }

    protected function createConnectionSettings(): ConnectionSettings
    {
        $connectionConfig = $this->getConnectionConfig()['connection_settings'];
        $lastWill = $connectionConfig['last_will'];

        return (new ConnectionSettings)
            ->setUsername($connectionConfig['username'] ?? null)
            ->setPassword($connectionConfig['password'] ?? null)
            ->setKeepAliveInterval($connectionConfig['keep_alive_interval'])
            ->setConnectTimeout($connectionConfig['connect_timeout'])
            ->setLastWillTopic($lastWill['topic'])
            ->setLastWillMessage($lastWill['message'])
            ->setLastWillQualityOfService($lastWill['quality_of_service'])
            ->setRetainLastWill($lastWill['retain'])
            ->setUseTls(false);
    }

    protected function handleSuccessfulConnection(): void
    {
        $this->isConnected = true;
        $this->reconnectAttempts = 0;
        $this->resubscribeToTopics();
        
        Cache::put('mqtt_status', 'connected', 60);
        Log::info('MQTT Connected successfully');
    }

    protected function resubscribeToTopics(): void
    {
        foreach ($this->subscriptions as $topic => $callback) {
            $this->client->subscribe($topic, $callback);
        }
    }

    protected function handleConnectionFailure(\Exception $e): void
    {
        Log::error('MQTT Connection failed: ' . $e->getMessage());
        $this->handleDisconnection();
    }

    public function subscribe(string $topic, callable $callback, int $qos = 0): bool
    {
        $this->subscriptions[$topic] = $callback;
        
        if (!$this->isConnected) {
            return false;
        }

        try {
            $this->client->subscribe($topic, $callback, $qos);
            Log::debug("MQTT Subscribed to {$topic}");
            return true;
        } catch (\Exception $e) {
            Log::error("MQTT Subscribe failed to {$topic}: " . $e->getMessage());
            return false;
        }
    }

    public function publish(string $topic, string $message, int $qos = 1, bool $retain = false): bool
    {
        try {
            // Ensure connection (lazy init)
            if (!$this->isConnected) {
                $this->initializeConnection();
            }

            if (!$this->isConnected && !$this->connect()) {
                $this->queueMessage($topic, $message, $qos, $retain);
                return false;
            }

            $this->client->publish($topic, $message, $qos, $retain);
            Log::debug("MQTT Published to {$topic}");
            return true;
        } catch (\Exception $e) {
            $this->handlePublishFailure($e, $topic, $message, $qos, $retain);
            return false;
        }
    }

    protected function handlePublishFailure(\Exception $e, string $topic, string $message, int $qos, bool $retain): void
    {
        Log::error("MQTT Publish failed to {$topic}: " . $e->getMessage());
        $this->handleDisconnection();
        $this->queueMessage($topic, $message, $qos, $retain);
    }

    protected function queueMessage(string $topic, string $message, int $qos, bool $retain): void
    {
        $queue = Cache::get('mqtt_message_queue', []);
        $queue[] = compact('topic', 'message', 'qos', 'retain') + [
            'attempts' => 0,
            'timestamp' => now()->toDateTimeString(),
        ];
        Cache::put('mqtt_message_queue', $queue, 3600);
    }

    public function processMessageQueue(): void
    {
        if (!$this->isConnected) {
            return;
        }

        $queue = Cache::get('mqtt_message_queue', []);
        $remainingMessages = [];

        foreach ($queue as $message) {
            if ($message['attempts'] >= 3) {
                continue;
            }

            try {
                $this->publish(
                    $message['topic'],
                    $message['message'],
                    $message['qos'],
                    $message['retain']
                );
            } catch (\Exception $e) {
                $message['attempts']++;
                $remainingMessages[] = $message;
            }
        }

        Cache::put('mqtt_message_queue', $remainingMessages, 3600);
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    protected function handleDisconnection(): void
    {
        $this->isConnected = false;
        Cache::put('mqtt_status', 'disconnected', 60);
        $this->scheduleReconnect();
    }

    protected function scheduleReconnect(): void
    {
        $reconnectConfig = $this->getConnectionConfig()['connection_settings']['auto_reconnect'];
        
        if (!$reconnectConfig['enabled']) {
            return;
        }
        
        if ($this->reconnectAttempts < $reconnectConfig['max_reconnect_attempts']) {
            $this->reconnectAttempts++;
            
            // Exponential backoff: 3s, 6s, 12s, 24s, 48s, max 60s
            $baseDelay = $reconnectConfig['delay_between_reconnect_attempts'];
            $delay = min($baseDelay * pow(2, $this->reconnectAttempts - 1), 60);
            
            Log::info("MQTT Reconnect scheduled: attempt {$this->reconnectAttempts}/{$reconnectConfig['max_reconnect_attempts']} in {$delay}s");
            
            // Store next reconnect time (don't block current request)
            Cache::put('mqtt_next_reconnect', time() + $delay, 300);
        } else {
            Log::warning('MQTT Max reconnect attempts reached - will retry on next request');
            $this->reconnectAttempts = 0; // Reset for next try
            Cache::put('mqtt_next_reconnect', time() + 60, 300);
        }
    }

    public function __destruct()
    {
        if ($this->isConnected) {
            $this->client->disconnect();
        }
    }
}
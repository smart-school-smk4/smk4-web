<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MqttService
{
    protected $client;
    protected $config;
    protected $isConnected = false;
    protected $reconnectAttempts = 0;
    protected $lastActivityTime;
    protected $connectionLock = false;

    public function __construct()
    {
        $this->config = config('mqtt.connections.bel_sekolah');
        $this->initializeConnection();
    }

    protected function initializeConnection(): void
    {
        try {
            $this->client = new MqttClient(
                $this->config['host'],
                $this->config['port'],
                $this->config['client_id'] . '_' . uniqid()
            );

            $this->connect();
        } catch (\Exception $e) {
            Log::error('MQTT Initialization failed: ' . $e->getMessage());
            $this->scheduleReconnect();
        }
    }

    public function connect(): bool
    {
        // Prevent multiple simultaneous connection attempts
        if ($this->connectionLock) {
            return false;
        }

        $this->connectionLock = true;

        try {
            if ($this->isConnected) {
                $this->connectionLock = false;
                return true;
            }

            $connectionSettings = (new ConnectionSettings)
                ->setKeepAliveInterval($this->config['connection_settings']['keep_alive_interval'])
                ->setConnectTimeout($this->config['connection_settings']['connect_timeout'])
                ->setLastWillTopic($this->config['connection_settings']['last_will']['topic'])
                ->setLastWillMessage($this->config['connection_settings']['last_will']['message'])
                ->setLastWillQualityOfService($this->config['connection_settings']['last_will']['quality_of_service'])
                ->setRetainLastWill($this->config['connection_settings']['last_will']['retain'])
                ->setUseTls(false);

            $this->client->connect($connectionSettings, true);
            $this->isConnected = true;
            $this->reconnectAttempts = 0;
            $this->lastActivityTime = time();

            // Store connection status in cache for UI
            Cache::put('mqtt_status', 'connected', 60);

            Log::info('MQTT Connected successfully to ' . $this->config['host']);
            $this->connectionLock = false;
            return true;
        } catch (\Exception $e) {
            Log::error('MQTT Connection failed: ' . $e->getMessage());
            $this->handleDisconnection();
            $this->connectionLock = false;
            return false;
        }
    }

    protected function checkConnection(): void
    {
        try {
            // Simple ping test with short timeout
            $this->client->publish($this->config['connection_settings']['last_will']['topic'], 'ping', 0, false);
            $this->lastActivityTime = time();
        } catch (\Exception $e) {
            $this->handleDisconnection();
        }
    }

    public function ensureConnected(): bool
    {
        if (!$this->isConnected) {
            return $this->connect();
        }

        // Check if connection is stale
        if ((time() - $this->lastActivityTime) > $this->config['connection_settings']['keep_alive_interval']) {
            $this->checkConnection();
        }

        return $this->isConnected;
    }

    protected function handleDisconnection(): void
    {
        $this->isConnected = false;
        Cache::put('mqtt_status', 'disconnected', 60);
        Log::warning('MQTT Disconnection detected');
        $this->scheduleReconnect();
    }

    protected function scheduleReconnect(): void
    {
        $maxAttempts = $this->config['connection_settings']['auto_reconnect']['max_reconnect_attempts'] ?? 5;
        $delay = $this->config['connection_settings']['auto_reconnect']['delay_between_reconnect_attempts'] ?? 2;

        if ($this->reconnectAttempts < $maxAttempts) {
            $this->reconnectAttempts++;
            $actualDelay = $delay * $this->reconnectAttempts;

            Log::info("Attempting MQTT reconnect ({$this->reconnectAttempts}/{$maxAttempts}) in {$actualDelay} seconds");

            sleep($actualDelay);
            $this->connect();
        } else {
            Log::error('MQTT Max reconnect attempts reached');
            Cache::put('mqtt_status', 'disconnected', 60);
        }
    }

    public function isConnected(): bool
    {
        return $this->isConnected;
    }

    public function publish($topic, $message, $qos = 0, $retain = false): bool
    {
        if (!$this->ensureConnected()) {
            // Store message in queue if disconnected
            $this->storeMessageInQueue($topic, $message, $qos, $retain);
            return false;
        }

        try {
            $this->client->publish($topic, $message, $qos, $retain);
            $this->lastActivityTime = time();
            Log::debug("MQTT Published to {$topic}: {$message}");
            return true;
        } catch (\Exception $e) {
            $this->handleDisconnection();
            Log::error("MQTT Publish failed to {$topic}: " . $e->getMessage());

            // Store message in queue if failed
            $this->storeMessageInQueue($topic, $message, $qos, $retain);
            return false;
        }
    }

    protected function storeMessageInQueue($topic, $message, $qos, $retain): void
    {
        $queue = Cache::get('mqtt_message_queue', []);
        $queue[] = [
            'topic' => $topic,
            'message' => $message,
            'qos' => $qos,
            'retain' => $retain,
            'attempts' => 0,
            'timestamp' => time(),
        ];
        Cache::put('mqtt_message_queue', $queue, 3600);
    }

    public function processMessageQueue(): void
    {
        if (!$this->isConnected()) {
            return;
        }

        $queue = Cache::get('mqtt_message_queue', []);
        $remainingMessages = [];

        foreach ($queue as $message) {
            if ($message['attempts'] >= 3) {
                continue; // Skip messages that failed too many times
            }

            try {
                $this->client->publish(
                    $message['topic'],
                    $message['message'],
                    $message['qos'],
                    $message['retain']
                );
                $this->lastActivityTime = time();
            } catch (\Exception $e) {
                $message['attempts']++;
                $remainingMessages[] = $message;
            }
        }

        Cache::put('mqtt_message_queue', $remainingMessages, 3600);
    }

    public function subscribe($topic, $callback, $qos = 0): bool
    {
        if (!$this->ensureConnected()) {
            return false;
        }

        try {
            $this->client->subscribe($topic, $callback, $qos);
            $this->lastActivityTime = time();
            Log::info("MQTT Subscribed to {$topic}");
            return true;
        } catch (\Exception $e) {
            $this->handleDisconnection();
            Log::error("MQTT Subscribe failed to {$topic}: " . $e->getMessage());
            return false;
        }
    }

    public function loop(bool $allowSleep = true): void
    {
        if ($this->isConnected()) {
            try {
                $this->client->loop($allowSleep);
                $this->lastActivityTime = time();
                $this->processMessageQueue();
            } catch (\Exception $e) {
                $this->handleDisconnection();
            }
        } else {
            $this->connect();
        }
    }

    public function disconnect(): void
    {
        if ($this->isConnected) {
            try {
                $this->client->disconnect();
                $this->isConnected = false;
                Cache::put('mqtt_status', 'disconnected', 60);
                Log::info('MQTT Disconnected gracefully');
            } catch (\Exception $e) {
                Log::error('MQTT Disconnection error: ' . $e->getMessage());
            }
        }
    }

    public static function quickPublish($topic, $message, $qos = 0, $retain = false): bool
    {
        try {
            $config = config('mqtt.connections.bel_sekolah');
    
            $mqtt = new MqttClient(
                $config['host'],
                $config['port'],
                'quick-publish-' . uniqid()
            );
    
            $connectionSettings = (new ConnectionSettings)
                ->setConnectTimeout($config['connection_settings']['connect_timeout'] ?? 2)
                ->setUseTls(false);
    
            $mqtt->connect($connectionSettings, true);
    
            $mqtt->publish($topic, $message, $qos, $retain);
            $mqtt->disconnect();
            return true;
        } catch (\Throwable $e) {
            Log::error('Quick MQTT publish failed: ' . $e->getMessage());
            return false;
        }
    }
    

    public function __destruct()
    {
        // Disconnect only if explicitly needed
        if ($this->isConnected) {
            $this->disconnect();
        }
    }
    
    public function sendAnnouncement($payload)
    {
        $topic = 'bel/sekolah/pengumuman';
        
        // Publish utama
        $this->publish($topic, json_encode($payload), 1, false);
        
        // Jika TTS, kirim perintah stop setelah delay
        if ($payload['type'] === 'tts' && $payload['auto_stop'] ?? false) {
            
            $stopPayload = [
                'type' => 'stop_tts',
                'target_ruangans' => $payload['target_ruangans'],
                'timestamp' => now()->toDateTimeString()
            ];
            $this->publish($topic, json_encode($stopPayload), 1, false);
        }
        
    }
}
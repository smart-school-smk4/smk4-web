<?php

declare(strict_types=1);

use PhpMqtt\Client\MqttClient;

return [
    'default_connection' => 'sekolah',

    'connections' => [
        'bel_sekolah' => [
            'host' => env('MQTT_HOST', 'localhost'),
            'port' => (int) env('MQTT_PORT', 1883),
            'client_id' => env('MQTT_CLIENT_ID', 'laravel_bel_' . bin2hex(random_bytes(4))),
            'use_clean_session' => false,
            'connection_settings' => [
                'last_will' => [
                    'topic' => 'bel/sekolah/status/backend',
                    'message' => json_encode(['status' => 'offline']),
                    'quality_of_service' => 1,
                    'retain' => true,
                ],
                'connect_timeout' => 10,
                'socket_timeout' => 5,
                'keep_alive_interval' => 60, 
            ],
        ],
    ],

    // Topic configuration
    'topics' => [
        'commands' => [
            'ring' => 'bel/sekolah/command/ring',
            'sync' => 'bel/sekolah/command/sync',
            'status' => 'bel/sekolah/command/status',
        ],
        'responses' => [
            'status' => 'bel/sekolah/response/status',
            'ack' => 'bel/sekolah/response/ack',
        ],
        'announcements' => [
            'general' => 'bel/sekolah/pengumuman',
            'emergency' => 'bel/sekolah/emergency',
            'feedback' => 'bel/sekolah/status/pengumuman',
        ],
    ],
];
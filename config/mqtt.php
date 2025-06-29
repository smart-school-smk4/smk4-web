<?php

declare(strict_types=1);

return [
    'default_connection' => 'bel_sekolah',

    'connections' => [
        'bel_sekolah' => [
            'host' => env('MQTT_HOST', '192.168.1.5'),
            'port' => (int) env('MQTT_PORT', 1883),
            'client_id' => env('MQTT_CLIENT_ID', 'laravel_bel_' . bin2hex(random_bytes(4))),
            'use_clean_session' => false,
            'connection_settings' => [
                'auto_reconnect' => [
                    'enabled' => true,
                    'max_reconnect_attempts' => 2,
                    'delay_between_reconnect_attempts' => 1,
                ],
                'last_will' => [
                    'topic' => 'bel/sekolah/status/backend',
                    'message' => json_encode(['status' => 'offline']),
                    'quality_of_service' => 1,
                    'retain' => true,
                ],
                'connect_timeout' => 5,
                'socket_timeout' => 5,
                'keep_alive_interval' => 10, 
            ],
        ],
    ],

    // Topic configuration
    'topics' => [
        'commands' => [
            'announcement' => 'announcement/command',
            'ring' => 'bel/sekolah/command/ring',
            'sync' => 'bel/sekolah/command/sync',
            'status' => 'bel/sekolah/command/status',
            'relay_control' => 'ruangan/+/relay/control',
        ],
        'responses' => [
            'announcement_ack' => 'announcement/response/ack',
            'announcement_error' => 'announcement/response/error',
            'status' => 'bel/sekolah/response/status',
            'ack' => 'bel/sekolah/response/ack',
            'relay_status' => 'ruangan/+/relay/status',
        ],
        'events' => [  // [!++ Add this section ++!]
            'bell_schedule' => 'bel/sekolah/events/schedule',
            'bell_manual' => 'bel/sekolah/events/manual'
        ],
    ],

    // QoS Levels
    'qos_levels' => [
        'default' => 0,
        'announcement' => 1,
        'relay_control' => 1, // QoS 1 untuk kontrol relay
        'status_updates' => 1,
    ],
];
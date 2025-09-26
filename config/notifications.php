<?php

return [
    'notifications' => [
        'queue' => env('RMS_NOTIFICATIONS_QUEUE', 'default'),
        'channels' => [
            'push' => [
                'enabled' => false, // per request: push notifications OFF by default
            ],
            'email' => [
                'enabled' => true,
            ],
            'telegram' => [
                'enabled' => true, // wiring later
                'bot_token' => env('RMS_TELEGRAM_BOT_TOKEN'),
                'default_chat_id' => env('RMS_TELEGRAM_CHAT_ID'),
            ],
            'webhook' => [
                'enabled' => true,
                'timeout' => 10,
                'signature_secret' => env('RMS_WEBHOOK_SIGNATURE'),
            ],
        ],
        'reminders' => [
            // default mode OFF: means immediate send unless schedule provided
            'default_mode' => 'off', // off | one_off | recurring
            // Polling window for due reminders (handled by console schedule)
            'max_batch' => 200,
        ],
    ],
];

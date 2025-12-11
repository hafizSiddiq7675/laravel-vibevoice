<?php

return [

    /*
    |--------------------------------------------------------------------------
    | VibeVoice API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the connection to your VibeVoice API server. The API server
    | should be running on GPU infrastructure with the VibeVoice model loaded.
    |
    */

    'api' => [
        'base_url' => env('VIBEVOICE_API_URL', 'http://localhost:8000'),
        'key' => env('VIBEVOICE_API_KEY'),
        'timeout' => env('VIBEVOICE_TIMEOUT', 120),
        'connect_timeout' => env('VIBEVOICE_CONNECT_TIMEOUT', 10),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Voice Configuration
    |--------------------------------------------------------------------------
    |
    | Set the default voice to use when generating audio. You can override
    | this on a per-request basis. Use the vibevoice:voices command to
    | see available voices.
    |
    */

    'default_voice' => env('VIBEVOICE_DEFAULT_VOICE', 'en-US-female-1'),

    /*
    |--------------------------------------------------------------------------
    | Audio Output Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how generated audio files are stored and formatted.
    |
    */

    'audio' => [
        'format' => env('VIBEVOICE_AUDIO_FORMAT', 'mp3'),
        'sample_rate' => env('VIBEVOICE_SAMPLE_RATE', 24000),
        'bitrate' => env('VIBEVOICE_BITRATE', 128),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Configuration
    |--------------------------------------------------------------------------
    |
    | Configure where generated audio files should be stored. Supports
    | any Laravel filesystem disk (local, s3, etc.).
    |
    */

    'storage' => [
        'disk' => env('VIBEVOICE_STORAGE_DISK', 'public'),
        'path' => env('VIBEVOICE_STORAGE_PATH', 'vibevoice'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for asynchronous audio generation.
    |
    */

    'queue' => [
        'connection' => env('VIBEVOICE_QUEUE_CONNECTION'),
        'queue' => env('VIBEVOICE_QUEUE_NAME', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Streaming Configuration
    |--------------------------------------------------------------------------
    |
    | Configure real-time streaming audio generation settings.
    |
    */

    'streaming' => [
        'enabled' => env('VIBEVOICE_STREAMING_ENABLED', true),
        'chunk_size' => env('VIBEVOICE_CHUNK_SIZE', 4096),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching for voice list and other API responses.
    |
    */

    'cache' => [
        'enabled' => env('VIBEVOICE_CACHE_ENABLED', true),
        'ttl' => env('VIBEVOICE_CACHE_TTL', 3600),
        'prefix' => 'vibevoice',
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configure retry behavior for failed API requests.
    |
    */

    'retry' => [
        'times' => env('VIBEVOICE_RETRY_TIMES', 3),
        'sleep' => env('VIBEVOICE_RETRY_SLEEP', 1000),
    ],

];

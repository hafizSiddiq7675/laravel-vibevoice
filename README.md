# Laravel VibeVoice

[![Latest Version on Packagist](https://img.shields.io/packagist/v/bitsoftsol/laravel-vibevoice.svg?style=flat-square)](https://packagist.org/packages/bitsoftsol/laravel-vibevoice)
[![Total Downloads](https://img.shields.io/packagist/dt/bitsoftsol/laravel-vibevoice.svg?style=flat-square)](https://packagist.org/packages/bitsoftsol/laravel-vibevoice)
[![License](https://img.shields.io/packagist/l/bitsoftsol/laravel-vibevoice.svg?style=flat-square)](https://packagist.org/packages/bitsoftsol/laravel-vibevoice)

A Laravel package for integrating Microsoft's VibeVoice AI voice synthesis technology. Generate high-quality, multi-speaker text-to-speech audio in your Laravel applications.

## Features

- Single speaker text-to-speech generation
- Multi-speaker conversation synthesis (up to 4 speakers)
- Real-time streaming audio generation
- Eloquent model integration via trait
- Asynchronous audio generation via Laravel queues
- Configurable storage (local, S3, etc.)
- Artisan commands for CLI usage

## Requirements

- PHP 8.1+
- Laravel 10.x or 11.x
- A running VibeVoice API server (see [vibevoice-api](https://github.com/bitsoftsol/vibevoice-api))

## Installation

Install the package via Composer:

```bash
composer require bitsoftsol/laravel-vibevoice
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=vibevoice-config
```

Add the following environment variables to your `.env` file:

```env
VIBEVOICE_API_URL=http://your-api-server:8000
VIBEVOICE_API_KEY=your-api-key
VIBEVOICE_DEFAULT_VOICE=en-US-female-1
```

## Quick Start

### Basic Usage

```php
use BitsoftSol\VibeVoice\Facades\VibeVoice;

// Generate audio from text
$result = VibeVoice::generate('Hello, welcome to our application!');

// Access the audio content
$audioContent = $result->getAudioContent();
$duration = $result->duration; // in seconds

// Generate and save to storage
$path = VibeVoice::generateAndSave('Hello world!', 'greeting');
// Returns: vibevoice/greeting.mp3
```

### Using a Specific Voice

```php
// List available voices
$voices = VibeVoice::voices();

foreach ($voices as $voice) {
    echo "{$voice->id}: {$voice->name} ({$voice->gender})\n";
}

// Generate with a specific voice
$result = VibeVoice::generate('Hello!', 'en-US-male-1');
```

### Multi-Speaker Conversations

```php
use BitsoftSol\VibeVoice\DTOs\ConversationLine;

$conversation = [
    new ConversationLine(text: 'Hello, how can I help you today?', voice: 'en-US-female-1', speaker: 'Agent'),
    new ConversationLine(text: 'I have a question about my order.', voice: 'en-US-male-1', speaker: 'Customer'),
    new ConversationLine(text: 'Of course! Let me look that up for you.', voice: 'en-US-female-1', speaker: 'Agent'),
];

$result = VibeVoice::conversation($conversation);

// Or using arrays
$conversation = [
    ['text' => 'Hello!', 'voice' => 'en-US-female-1'],
    ['text' => 'Hi there!', 'voice' => 'en-US-male-1'],
];

$path = VibeVoice::conversationAndSave($conversation, 'dialogue');
```

### Streaming Audio

```php
// Stream audio chunks in real-time
foreach (VibeVoice::stream('This is a long text to stream...') as $chunk) {
    // Process each audio chunk
    echo $chunk;
}
```

## Model Integration

Add text-to-speech capabilities to your Eloquent models using the `HasVoiceContent` trait.

### Migration

```php
Schema::create('articles', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('content');
    $table->string('audio_file')->nullable();
    $table->float('audio_duration')->nullable();
    $table->string('voice_id')->nullable();
    $table->timestamps();
});
```

### Model Setup

```php
use BitsoftSol\VibeVoice\Traits\HasVoiceContent;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasVoiceContent;

    protected $fillable = ['title', 'content', 'voice_id'];

    // Specify which field contains the text to convert
    public function getVoiceTextField(): string
    {
        return 'content';
    }

    // Optionally specify a default voice for this model
    public function getVoiceId(): ?string
    {
        return $this->voice_id ?? 'en-US-female-1';
    }
}
```

### Usage

```php
$article = Article::create([
    'title' => 'My Article',
    'content' => 'This is the article content that will be converted to speech.',
]);

// Generate audio synchronously
$article->generateAudio();

// Generate audio asynchronously via queue
$article->generateAudioAsync();

// Check if audio exists
if ($article->hasAudio()) {
    $url = $article->getAudioUrl();
    $duration = $article->getFormattedAudioDuration(); // "02:30.50"
}

// Regenerate audio (deletes old file and creates new)
$article->regenerateAudio();

// Delete audio file
$article->deleteAudio();
```

## Async Processing

### Queue Jobs

```php
use BitsoftSol\VibeVoice\Facades\VibeVoice;

// Generate audio in background
VibeVoice::generateAsync('Long text to process...', 'en-US-female-1', 'output-file');

// Generate conversation in background
VibeVoice::conversationAsync($lines, 'conversation-output');
```

### Events

Listen for audio generation events:

```php
// In EventServiceProvider or event listener
use BitsoftSol\VibeVoice\Events\VoiceGenerated;
use BitsoftSol\VibeVoice\Events\VoiceGenerationFailed;

Event::listen(VoiceGenerated::class, function ($event) {
    $path = $event->path;
    $duration = $event->result->duration;

    // Update related model if available
    if ($model = $event->getModel()) {
        // Model was updated automatically
    }
});

Event::listen(VoiceGenerationFailed::class, function ($event) {
    Log::error('Voice generation failed', [
        'error' => $event->getErrorMessage(),
        'text' => $event->text,
    ]);
});
```

## Artisan Commands

### Generate Audio

```bash
# Generate audio from text
php artisan vibevoice:generate "Hello, this is a test!"

# Specify voice and output file
php artisan vibevoice:generate "Hello!" --voice=en-US-male-1 --output=greeting

# Generate asynchronously
php artisan vibevoice:generate "Hello!" --async
```

### List Voices

```bash
# List all available voices
php artisan vibevoice:voices

# Filter by language or gender
php artisan vibevoice:voices --language=en-US --gender=female

# Output as JSON
php artisan vibevoice:voices --json
```

### Test Connection

```bash
# Basic connection test
php artisan vibevoice:test

# Full test including audio generation
php artisan vibevoice:test --full
```

## Configuration

```php
// config/vibevoice.php

return [
    'api' => [
        'base_url' => env('VIBEVOICE_API_URL', 'http://localhost:8000'),
        'key' => env('VIBEVOICE_API_KEY'),
        'timeout' => env('VIBEVOICE_TIMEOUT', 120),
        'connect_timeout' => env('VIBEVOICE_CONNECT_TIMEOUT', 10),
    ],

    'default_voice' => env('VIBEVOICE_DEFAULT_VOICE', 'en-US-female-1'),

    'audio' => [
        'format' => env('VIBEVOICE_AUDIO_FORMAT', 'mp3'),
        'sample_rate' => env('VIBEVOICE_SAMPLE_RATE', 24000),
        'bitrate' => env('VIBEVOICE_BITRATE', 128),
    ],

    'storage' => [
        'disk' => env('VIBEVOICE_STORAGE_DISK', 'public'),
        'path' => env('VIBEVOICE_STORAGE_PATH', 'vibevoice'),
    ],

    'queue' => [
        'connection' => env('VIBEVOICE_QUEUE_CONNECTION'),
        'queue' => env('VIBEVOICE_QUEUE_NAME', 'default'),
    ],

    'streaming' => [
        'enabled' => env('VIBEVOICE_STREAMING_ENABLED', true),
        'chunk_size' => env('VIBEVOICE_CHUNK_SIZE', 4096),
    ],

    'cache' => [
        'enabled' => env('VIBEVOICE_CACHE_ENABLED', true),
        'ttl' => env('VIBEVOICE_CACHE_TTL', 3600),
        'prefix' => 'vibevoice',
    ],

    'retry' => [
        'times' => env('VIBEVOICE_RETRY_TIMES', 3),
        'sleep' => env('VIBEVOICE_RETRY_SLEEP', 1000),
    ],
];
```

## Error Handling

The package throws specific exceptions for different error scenarios:

```php
use BitsoftSol\VibeVoice\Exceptions\AuthenticationException;
use BitsoftSol\VibeVoice\Exceptions\ConnectionException;
use BitsoftSol\VibeVoice\Exceptions\GenerationException;
use BitsoftSol\VibeVoice\Exceptions\RateLimitException;

try {
    $result = VibeVoice::generate($text);
} catch (AuthenticationException $e) {
    // Invalid or missing API key
} catch (ConnectionException $e) {
    // Cannot connect to API server
} catch (RateLimitException $e) {
    $retryAfter = $e->getRetryAfter(); // seconds to wait
} catch (GenerationException $e) {
    // Invalid input or generation failed
    $context = $e->getContext();
}
```

## API Reference

### Facade Methods

| Method | Description |
|--------|-------------|
| `generate(string $text, ?string $voice = null): AudioResult` | Generate audio from text |
| `conversation(array $lines): AudioResult` | Generate multi-speaker audio |
| `stream(string $text, ?string $voice = null): Generator` | Stream audio chunks |
| `voices(): array` | Get available voices |
| `isHealthy(): bool` | Check API health |
| `health(): array` | Get API health details |
| `generateAndSave(string $text, ?string $filename = null, ?string $voice = null): string` | Generate and save audio |
| `generateAsync(string $text, ?string $voice = null, ?string $filename = null): void` | Queue audio generation |

### HasVoiceContent Trait Methods

| Method | Description |
|--------|-------------|
| `generateAudio(?string $voice = null): AudioResult` | Generate audio synchronously |
| `generateAudioAsync(?string $voice = null): void` | Generate audio via queue |
| `hasAudio(): bool` | Check if audio exists |
| `getAudioUrl(): ?string` | Get audio file URL |
| `getAudioPath(): ?string` | Get audio file path |
| `deleteAudio(): bool` | Delete audio file |
| `regenerateAudio(?string $voice = null): AudioResult` | Delete and regenerate audio |

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details.

## Security

If you discover any security-related issues, please email security@bitsoftsol.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Credits

- [BitsoftSol Development Team](https://github.com/bitsoftsol)
- [All Contributors](../../contributors)

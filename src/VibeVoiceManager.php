<?php

namespace BitsoftSol\VibeVoice;

use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\DTOs\ConversationLine;
use BitsoftSol\VibeVoice\DTOs\Voice;
use BitsoftSol\VibeVoice\Jobs\GenerateVoiceJob;
use Generator;
use Illuminate\Support\Facades\Storage;

class VibeVoiceManager
{
    protected VibeVoiceClientInterface $client;
    protected array $config;

    public function __construct(VibeVoiceClientInterface $client, array $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    /**
     * Generate audio from text using a single voice.
     *
     * @param string $text The text to convert to speech
     * @param string|null $voice The voice identifier to use
     * @return AudioResult
     */
    public function generate(string $text, ?string $voice = null): AudioResult
    {
        return $this->client->generate($text, $voice);
    }

    /**
     * Generate audio from a multi-speaker conversation.
     *
     * @param array<ConversationLine|array> $lines Array of conversation lines
     * @return AudioResult
     */
    public function conversation(array $lines): AudioResult
    {
        return $this->client->conversation($lines);
    }

    /**
     * Stream audio generation in real-time.
     *
     * @param string $text The text to convert to speech
     * @param string|null $voice The voice identifier to use
     * @return Generator Yields audio chunks
     */
    public function stream(string $text, ?string $voice = null): Generator
    {
        return $this->client->stream($text, $voice);
    }

    /**
     * Get list of available voices.
     *
     * @return array<Voice>
     */
    public function voices(): array
    {
        return $this->client->voices();
    }

    /**
     * Check API server health.
     *
     * @return bool
     */
    public function isHealthy(): bool
    {
        return $this->client->isHealthy();
    }

    /**
     * Get API server health details.
     *
     * @return array
     */
    public function health(): array
    {
        return $this->client->health();
    }

    /**
     * Generate audio and save to storage.
     *
     * @param string $text The text to convert to speech
     * @param string|null $filename Custom filename (without extension)
     * @param string|null $voice The voice identifier to use
     * @return string The path to the saved file
     */
    public function generateAndSave(string $text, ?string $filename = null, ?string $voice = null): string
    {
        $result = $this->generate($text, $voice);

        return $this->saveAudio($result, $filename);
    }

    /**
     * Generate conversation audio and save to storage.
     *
     * @param array<ConversationLine|array> $lines Array of conversation lines
     * @param string|null $filename Custom filename (without extension)
     * @return string The path to the saved file
     */
    public function conversationAndSave(array $lines, ?string $filename = null): string
    {
        $result = $this->conversation($lines);

        return $this->saveAudio($result, $filename);
    }

    /**
     * Save audio result to storage.
     *
     * @param AudioResult $result The audio result to save
     * @param string|null $filename Custom filename (without extension)
     * @return string The path to the saved file
     */
    public function saveAudio(AudioResult $result, ?string $filename = null): string
    {
        $disk = $this->config['storage']['disk'];
        $basePath = $this->config['storage']['path'];

        $filename = $filename ?? $this->generateFilename();
        $extension = $result->format;
        $fullPath = "{$basePath}/{$filename}.{$extension}";

        Storage::disk($disk)->put($fullPath, $result->getAudioContent());

        return $fullPath;
    }

    /**
     * Dispatch a job to generate audio asynchronously.
     *
     * @param string $text The text to convert to speech
     * @param string|null $voice The voice identifier to use
     * @param string|null $filename Custom filename (without extension)
     * @return void
     */
    public function generateAsync(string $text, ?string $voice = null, ?string $filename = null): void
    {
        $job = new GenerateVoiceJob($text, $voice, $filename);

        if ($this->config['queue']['connection']) {
            $job->onConnection($this->config['queue']['connection']);
        }

        if ($this->config['queue']['queue']) {
            $job->onQueue($this->config['queue']['queue']);
        }

        dispatch($job);
    }

    /**
     * Dispatch a job to generate conversation audio asynchronously.
     *
     * @param array<ConversationLine|array> $lines Array of conversation lines
     * @param string|null $filename Custom filename (without extension)
     * @return void
     */
    public function conversationAsync(array $lines, ?string $filename = null): void
    {
        $job = new GenerateVoiceJob($lines, null, $filename, true);

        if ($this->config['queue']['connection']) {
            $job->onConnection($this->config['queue']['connection']);
        }

        if ($this->config['queue']['queue']) {
            $job->onQueue($this->config['queue']['queue']);
        }

        dispatch($job);
    }

    /**
     * Get the underlying client instance.
     *
     * @return VibeVoiceClientInterface
     */
    public function client(): VibeVoiceClientInterface
    {
        return $this->client;
    }

    /**
     * Get the current configuration.
     *
     * @return array
     */
    public function config(): array
    {
        return $this->config;
    }

    /**
     * Generate a unique filename for audio files.
     *
     * @return string
     */
    protected function generateFilename(): string
    {
        return 'vibevoice_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
    }
}

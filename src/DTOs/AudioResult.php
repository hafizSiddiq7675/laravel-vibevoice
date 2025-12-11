<?php

namespace BitsoftSol\VibeVoice\DTOs;

use JsonSerializable;

class AudioResult implements JsonSerializable
{
    public function __construct(
        public readonly string $content,
        public readonly string $format,
        public readonly float $duration,
        public readonly int $sampleRate,
        public readonly ?string $voice = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * Create an AudioResult instance from API response array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            content: $data['audio'] ?? $data['content'] ?? '',
            format: $data['format'] ?? 'mp3',
            duration: (float) ($data['duration'] ?? 0),
            sampleRate: (int) ($data['sample_rate'] ?? 24000),
            voice: $data['voice'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * Get the raw audio content (base64 decoded if necessary).
     */
    public function getAudioContent(): string
    {
        if ($this->isBase64Encoded()) {
            return base64_decode($this->content);
        }

        return $this->content;
    }

    /**
     * Check if the content is base64 encoded.
     */
    public function isBase64Encoded(): bool
    {
        return base64_encode(base64_decode($this->content, true)) === $this->content;
    }

    /**
     * Get duration in a human-readable format.
     */
    public function getFormattedDuration(): string
    {
        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        return sprintf('%02d:%05.2f', $minutes, $seconds);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'content' => $this->content,
            'format' => $this->format,
            'duration' => $this->duration,
            'sample_rate' => $this->sampleRate,
            'voice' => $this->voice,
            'metadata' => $this->metadata,
        ];
    }

    /**
     * JSON serialization.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

<?php

namespace BitsoftSol\VibeVoice\DTOs;

use JsonSerializable;

class ConversationLine implements JsonSerializable
{
    public function __construct(
        public readonly string $text,
        public readonly string $voice,
        public readonly ?string $speaker = null,
        public readonly array $options = [],
    ) {}

    /**
     * Create a ConversationLine instance from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            text: $data['text'],
            voice: $data['voice'],
            speaker: $data['speaker'] ?? null,
            options: $data['options'] ?? [],
        );
    }

    /**
     * Create multiple ConversationLine instances from array of arrays.
     *
     * @param array $lines
     * @return array<ConversationLine>
     */
    public static function fromArrayMultiple(array $lines): array
    {
        return array_map(fn($line) => self::fromArray($line), $lines);
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'text' => $this->text,
            'voice' => $this->voice,
            'speaker' => $this->speaker,
            'options' => $this->options,
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

<?php

namespace BitsoftSol\VibeVoice\DTOs;

use JsonSerializable;

class Voice implements JsonSerializable
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $language,
        public readonly string $gender,
        public readonly ?string $description = null,
        public readonly ?string $preview_url = null,
        public readonly array $metadata = [],
    ) {}

    /**
     * Create a Voice instance from API response array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            language: $data['language'] ?? 'en-US',
            gender: $data['gender'] ?? 'neutral',
            description: $data['description'] ?? null,
            preview_url: $data['preview_url'] ?? null,
            metadata: $data['metadata'] ?? [],
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'language' => $this->language,
            'gender' => $this->gender,
            'description' => $this->description,
            'preview_url' => $this->preview_url,
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

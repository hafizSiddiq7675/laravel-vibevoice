<?php

namespace BitsoftSol\VibeVoice\Contracts;

use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\DTOs\ConversationLine;
use BitsoftSol\VibeVoice\DTOs\Voice;
use Generator;

interface VibeVoiceClientInterface
{
    /**
     * Generate audio from text using a single voice.
     *
     * @param string $text The text to convert to speech
     * @param string|null $voice The voice identifier to use
     * @return AudioResult
     */
    public function generate(string $text, ?string $voice = null): AudioResult;

    /**
     * Generate audio from a multi-speaker conversation.
     *
     * @param array<ConversationLine> $lines Array of conversation lines with speaker assignments
     * @return AudioResult
     */
    public function conversation(array $lines): AudioResult;

    /**
     * Stream audio generation in real-time.
     *
     * @param string $text The text to convert to speech
     * @param string|null $voice The voice identifier to use
     * @return Generator Yields audio chunks
     */
    public function stream(string $text, ?string $voice = null): Generator;

    /**
     * Get list of available voices.
     *
     * @return array<Voice>
     */
    public function voices(): array;

    /**
     * Check API server health.
     *
     * @return bool
     */
    public function isHealthy(): bool;

    /**
     * Get API server health details.
     *
     * @return array
     */
    public function health(): array;
}

<?php

namespace BitsoftSol\VibeVoice\Exceptions;

class GenerationException extends VibeVoiceException
{
    /**
     * Create an exception for invalid voice.
     */
    public static function invalidVoice(string $voice): self
    {
        return new self(
            "The voice '{$voice}' is not available. Use VibeVoice::voices() to see available voices.",
            400,
            null,
            ['voice' => $voice]
        );
    }

    /**
     * Create an exception for empty text.
     */
    public static function emptyText(): self
    {
        return new self(
            'Cannot generate audio from empty text.',
            400
        );
    }

    /**
     * Create an exception for text too long.
     */
    public static function textTooLong(int $length, int $maxLength): self
    {
        return new self(
            "Text length ({$length} characters) exceeds maximum allowed length ({$maxLength} characters).",
            400,
            null,
            ['length' => $length, 'max_length' => $maxLength]
        );
    }

    /**
     * Create an exception for API generation failure.
     */
    public static function failed(string $reason = ''): self
    {
        $message = 'Failed to generate audio.';
        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        return new self($message, 500, null, ['reason' => $reason]);
    }

    /**
     * Create an exception for too many speakers.
     */
    public static function tooManySpeakers(int $count, int $max = 4): self
    {
        return new self(
            "Too many speakers ({$count}). Maximum allowed is {$max}.",
            400,
            null,
            ['count' => $count, 'max' => $max]
        );
    }
}

<?php

namespace BitsoftSol\VibeVoice\Exceptions;

class AuthenticationException extends VibeVoiceException
{
    /**
     * Create an invalid API key exception.
     */
    public static function invalidApiKey(): self
    {
        return new self(
            'Invalid or missing API key. Please check your VIBEVOICE_API_KEY configuration.',
            401
        );
    }

    /**
     * Create an expired API key exception.
     */
    public static function expiredApiKey(): self
    {
        return new self(
            'The API key has expired. Please obtain a new API key.',
            401
        );
    }

    /**
     * Create a missing API key exception.
     */
    public static function missingApiKey(): self
    {
        return new self(
            'No API key configured. Please set VIBEVOICE_API_KEY in your environment.',
            401
        );
    }
}

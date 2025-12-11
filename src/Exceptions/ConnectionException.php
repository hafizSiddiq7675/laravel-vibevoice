<?php

namespace BitsoftSol\VibeVoice\Exceptions;

class ConnectionException extends VibeVoiceException
{
    /**
     * Create a connection timeout exception.
     */
    public static function timeout(string $url, int $timeout): self
    {
        return new self(
            "Connection to VibeVoice API at '{$url}' timed out after {$timeout} seconds.",
            408,
            null,
            ['url' => $url, 'timeout' => $timeout]
        );
    }

    /**
     * Create a connection refused exception.
     */
    public static function refused(string $url): self
    {
        return new self(
            "Connection to VibeVoice API at '{$url}' was refused. Please ensure the API server is running.",
            503,
            null,
            ['url' => $url]
        );
    }

    /**
     * Create a generic connection failure exception.
     */
    public static function failed(string $url, string $reason = ''): self
    {
        $message = "Failed to connect to VibeVoice API at '{$url}'.";
        if ($reason) {
            $message .= " Reason: {$reason}";
        }

        return new self($message, 500, null, ['url' => $url, 'reason' => $reason]);
    }
}

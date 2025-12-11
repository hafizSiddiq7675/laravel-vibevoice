<?php

namespace BitsoftSol\VibeVoice\Exceptions;

class RateLimitException extends VibeVoiceException
{
    protected ?int $retryAfter = null;

    public function __construct(
        string $message = 'Rate limit exceeded.',
        int $code = 429,
        ?\Exception $previous = null,
        array $context = [],
        ?int $retryAfter = null
    ) {
        parent::__construct($message, $code, $previous, $context);
        $this->retryAfter = $retryAfter;
    }

    /**
     * Get the number of seconds to wait before retrying.
     */
    public function getRetryAfter(): ?int
    {
        return $this->retryAfter;
    }

    /**
     * Create a rate limit exceeded exception.
     */
    public static function exceeded(?int $retryAfter = null): self
    {
        $message = 'Rate limit exceeded for VibeVoice API.';
        if ($retryAfter) {
            $message .= " Please retry after {$retryAfter} seconds.";
        }

        return new self(
            $message,
            429,
            null,
            ['retry_after' => $retryAfter],
            $retryAfter
        );
    }
}

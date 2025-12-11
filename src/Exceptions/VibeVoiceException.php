<?php

namespace BitsoftSol\VibeVoice\Exceptions;

use Exception;

class VibeVoiceException extends Exception
{
    protected array $context = [];

    public function __construct(
        string $message = '',
        int $code = 0,
        ?Exception $previous = null,
        array $context = []
    ) {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    /**
     * Get additional context about the exception.
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Create an exception with context.
     */
    public static function withContext(string $message, array $context = [], int $code = 0): self
    {
        return new self($message, $code, null, $context);
    }
}

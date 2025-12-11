<?php

namespace BitsoftSol\VibeVoice\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class VoiceGenerationFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Throwable $exception The exception that caused the failure
     * @param string|array $text The text or conversation lines that failed to generate
     * @param string|null $voice The voice that was requested
     * @param string|null $modelClass The model class (if generated for a model)
     * @param int|string|null $modelId The model ID (if generated for a model)
     */
    public function __construct(
        public readonly Throwable $exception,
        public readonly string|array $text,
        public readonly ?string $voice = null,
        public readonly ?string $modelClass = null,
        public readonly int|string|null $modelId = null,
    ) {}

    /**
     * Get the model instance if this was generated for a model.
     *
     * @return mixed|null
     */
    public function getModel(): mixed
    {
        if ($this->modelClass && $this->modelId) {
            return $this->modelClass::find($this->modelId);
        }

        return null;
    }

    /**
     * Get the error message.
     */
    public function getErrorMessage(): string
    {
        return $this->exception->getMessage();
    }
}

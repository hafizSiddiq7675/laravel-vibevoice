<?php

namespace BitsoftSol\VibeVoice\Events;

use BitsoftSol\VibeVoice\DTOs\AudioResult;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VoiceGenerated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param AudioResult $result The generated audio result
     * @param string $path The path where the audio was saved
     * @param string|null $modelClass The model class (if generated for a model)
     * @param int|string|null $modelId The model ID (if generated for a model)
     */
    public function __construct(
        public readonly AudioResult $result,
        public readonly string $path,
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
}

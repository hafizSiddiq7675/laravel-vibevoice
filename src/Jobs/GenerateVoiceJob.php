<?php

namespace BitsoftSol\VibeVoice\Jobs;

use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\Events\VoiceGenerated;
use BitsoftSol\VibeVoice\Events\VoiceGenerationFailed;
use BitsoftSol\VibeVoice\Facades\VibeVoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class GenerateVoiceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 10;

    /**
     * Create a new job instance.
     *
     * @param string|array $text The text or conversation lines to convert
     * @param string|null $voice The voice to use (null for default)
     * @param string|null $filename Custom filename for the audio
     * @param bool $isConversation Whether this is a multi-speaker conversation
     * @param string|null $modelClass The model class to update after generation
     * @param int|string|null $modelId The model ID to update after generation
     */
    public function __construct(
        public readonly string|array $text,
        public readonly ?string $voice = null,
        public readonly ?string $filename = null,
        public readonly bool $isConversation = false,
        public readonly ?string $modelClass = null,
        public readonly int|string|null $modelId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->isConversation) {
                $result = VibeVoice::conversation($this->text);
            } else {
                $result = VibeVoice::generate($this->text, $this->voice);
            }

            $path = VibeVoice::saveAudio($result, $this->filename);

            // Update the model if specified
            if ($this->modelClass && $this->modelId) {
                $this->updateModel($result, $path);
            }

            // Dispatch success event
            event(new VoiceGenerated(
                result: $result,
                path: $path,
                modelClass: $this->modelClass,
                modelId: $this->modelId
            ));
        } catch (Throwable $e) {
            $this->handleFailure($e);
            throw $e;
        }
    }

    /**
     * Update the associated model with the generated audio.
     */
    protected function updateModel(AudioResult $result, string $path): void
    {
        $model = $this->modelClass::find($this->modelId);

        if ($model) {
            // Check if model uses HasVoiceContent trait
            if (method_exists($model, 'getAudioFileField')) {
                $audioFileField = $model->getAudioFileField();
                $audioDurationField = $model->getAudioDurationField();
            } else {
                $audioFileField = 'audio_file';
                $audioDurationField = 'audio_duration';
            }

            $model->{$audioFileField} = $path;
            $model->{$audioDurationField} = $result->duration;
            $model->save();
        }
    }

    /**
     * Handle a job failure.
     */
    protected function handleFailure(Throwable $e): void
    {
        event(new VoiceGenerationFailed(
            exception: $e,
            text: $this->text,
            voice: $this->voice,
            modelClass: $this->modelClass,
            modelId: $this->modelId
        ));
    }

    /**
     * Handle a job failure (called by Laravel).
     */
    public function failed(Throwable $exception): void
    {
        $this->handleFailure($exception);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        $tags = ['vibevoice'];

        if ($this->isConversation) {
            $tags[] = 'conversation';
        }

        if ($this->modelClass) {
            $tags[] = $this->modelClass . ':' . $this->modelId;
        }

        return $tags;
    }
}

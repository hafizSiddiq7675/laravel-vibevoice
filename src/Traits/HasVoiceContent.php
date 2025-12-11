<?php

namespace BitsoftSol\VibeVoice\Traits;

use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\Facades\VibeVoice;
use BitsoftSol\VibeVoice\Jobs\GenerateVoiceJob;
use Illuminate\Support\Facades\Storage;

/**
 * Trait HasVoiceContent
 *
 * Add this trait to Eloquent models that need text-to-speech capabilities.
 *
 * Required database columns:
 * - audio_file: string|null - Path to the generated audio file
 * - audio_duration: float|null - Duration of the audio in seconds
 *
 * Optional: Override getVoiceTextField() to specify which field contains the text.
 * Optional: Override getVoiceId() to specify which voice to use.
 */
trait HasVoiceContent
{
    /**
     * Initialize the trait.
     */
    public function initializeHasVoiceContent(): void
    {
        // Add audio fields to fillable if not already present
        if (property_exists($this, 'fillable')) {
            $this->fillable = array_unique(array_merge($this->fillable, [
                $this->getAudioFileField(),
                $this->getAudioDurationField(),
            ]));
        }

        // Add casts for audio duration
        if (property_exists($this, 'casts')) {
            $this->casts[$this->getAudioDurationField()] = 'float';
        }
    }

    /**
     * Get the field name that contains the text to convert to speech.
     * Override this method to use a different field.
     */
    public function getVoiceTextField(): string
    {
        return 'content';
    }

    /**
     * Get the voice ID to use for this model.
     * Override this method to use a specific voice.
     */
    public function getVoiceId(): ?string
    {
        // Check if model has a voice_id field
        if (isset($this->voice_id)) {
            return $this->voice_id;
        }

        return null; // Use default voice from config
    }

    /**
     * Get the field name for storing the audio file path.
     */
    public function getAudioFileField(): string
    {
        return 'audio_file';
    }

    /**
     * Get the field name for storing the audio duration.
     */
    public function getAudioDurationField(): string
    {
        return 'audio_duration';
    }

    /**
     * Get the text content to convert to speech.
     */
    public function getVoiceText(): string
    {
        $field = $this->getVoiceTextField();

        return $this->{$field} ?? '';
    }

    /**
     * Generate audio for this model synchronously.
     *
     * @param string|null $voice Override the voice to use
     * @return AudioResult
     */
    public function generateAudio(?string $voice = null): AudioResult
    {
        $voice = $voice ?? $this->getVoiceId();
        $text = $this->getVoiceText();

        $result = VibeVoice::generate($text, $voice);

        // Generate filename based on model
        $filename = $this->generateAudioFilename();

        // Save the audio file
        $path = VibeVoice::saveAudio($result, $filename);

        // Update the model
        $this->{$this->getAudioFileField()} = $path;
        $this->{$this->getAudioDurationField()} = $result->duration;
        $this->save();

        return $result;
    }

    /**
     * Generate audio for this model asynchronously via queue.
     *
     * @param string|null $voice Override the voice to use
     * @return void
     */
    public function generateAudioAsync(?string $voice = null): void
    {
        $voice = $voice ?? $this->getVoiceId();
        $text = $this->getVoiceText();
        $filename = $this->generateAudioFilename();

        $job = new GenerateVoiceJob(
            text: $text,
            voice: $voice,
            filename: $filename,
            isConversation: false,
            modelClass: static::class,
            modelId: $this->getKey()
        );

        $config = config('vibevoice');

        if ($config['queue']['connection']) {
            $job->onConnection($config['queue']['connection']);
        }

        if ($config['queue']['queue']) {
            $job->onQueue($config['queue']['queue']);
        }

        dispatch($job);
    }

    /**
     * Check if audio has been generated for this model.
     */
    public function hasAudio(): bool
    {
        $audioFile = $this->{$this->getAudioFileField()};

        return !empty($audioFile);
    }

    /**
     * Get the URL to the audio file.
     */
    public function getAudioUrl(): ?string
    {
        $audioFile = $this->{$this->getAudioFileField()};

        if (empty($audioFile)) {
            return null;
        }

        $disk = config('vibevoice.storage.disk', 'public');

        return Storage::disk($disk)->url($audioFile);
    }

    /**
     * Get the full path to the audio file.
     */
    public function getAudioPath(): ?string
    {
        $audioFile = $this->{$this->getAudioFileField()};

        if (empty($audioFile)) {
            return null;
        }

        $disk = config('vibevoice.storage.disk', 'public');

        return Storage::disk($disk)->path($audioFile);
    }

    /**
     * Delete the audio file associated with this model.
     *
     * @return bool
     */
    public function deleteAudio(): bool
    {
        $audioFile = $this->{$this->getAudioFileField()};

        if (empty($audioFile)) {
            return false;
        }

        $disk = config('vibevoice.storage.disk', 'public');

        if (Storage::disk($disk)->exists($audioFile)) {
            Storage::disk($disk)->delete($audioFile);
        }

        $this->{$this->getAudioFileField()} = null;
        $this->{$this->getAudioDurationField()} = null;
        $this->save();

        return true;
    }

    /**
     * Regenerate audio for this model.
     *
     * @param string|null $voice Override the voice to use
     * @return AudioResult
     */
    public function regenerateAudio(?string $voice = null): AudioResult
    {
        $this->deleteAudio();

        return $this->generateAudio($voice);
    }

    /**
     * Generate a unique filename for the audio file based on the model.
     */
    protected function generateAudioFilename(): string
    {
        $modelName = class_basename($this);
        $id = $this->getKey() ?? 'new';

        return strtolower("{$modelName}_{$id}_" . date('Ymd_His'));
    }

    /**
     * Get the formatted audio duration.
     */
    public function getFormattedAudioDuration(): ?string
    {
        $duration = $this->{$this->getAudioDurationField()};

        if ($duration === null) {
            return null;
        }

        $minutes = floor($duration / 60);
        $seconds = $duration % 60;

        return sprintf('%02d:%05.2f', $minutes, $seconds);
    }
}

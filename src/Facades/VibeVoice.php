<?php

namespace BitsoftSol\VibeVoice\Facades;

use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\DTOs\Voice;
use BitsoftSol\VibeVoice\VibeVoiceManager;
use Generator;
use Illuminate\Support\Facades\Facade;

/**
 * @method static AudioResult generate(string $text, ?string $voice = null)
 * @method static AudioResult conversation(array $lines)
 * @method static Generator stream(string $text, ?string $voice = null)
 * @method static array<Voice> voices()
 * @method static bool isHealthy()
 * @method static array health()
 * @method static string generateAndSave(string $text, ?string $filename = null, ?string $voice = null)
 * @method static string conversationAndSave(array $lines, ?string $filename = null)
 * @method static string saveAudio(AudioResult $result, ?string $filename = null)
 * @method static void generateAsync(string $text, ?string $voice = null, ?string $filename = null)
 * @method static void conversationAsync(array $lines, ?string $filename = null)
 * @method static VibeVoiceClientInterface client()
 * @method static array config()
 *
 * @see \BitsoftSol\VibeVoice\VibeVoiceManager
 */
class VibeVoice extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return VibeVoiceManager::class;
    }
}

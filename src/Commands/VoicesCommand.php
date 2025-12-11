<?php

namespace BitsoftSol\VibeVoice\Commands;

use BitsoftSol\VibeVoice\Facades\VibeVoice;
use Illuminate\Console\Command;

class VoicesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'vibevoice:voices
                            {--language= : Filter voices by language (e.g., en-US)}
                            {--gender= : Filter voices by gender (male, female, neutral)}
                            {--json : Output as JSON}';

    /**
     * The console command description.
     */
    protected $description = 'List available voices from the VibeVoice API';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        try {
            $this->info('Fetching available voices...');
            $this->newLine();

            $voices = VibeVoice::voices();

            // Apply filters
            $language = $this->option('language');
            $gender = $this->option('gender');

            if ($language) {
                $voices = array_filter($voices, fn($v) => stripos($v->language, $language) !== false);
            }

            if ($gender) {
                $voices = array_filter($voices, fn($v) => strtolower($v->gender) === strtolower($gender));
            }

            $voices = array_values($voices); // Re-index array

            if (empty($voices)) {
                $this->warn('No voices found matching the criteria.');
                return self::SUCCESS;
            }

            // Output as JSON if requested
            if ($this->option('json')) {
                $this->line(json_encode(
                    array_map(fn($v) => $v->toArray(), $voices),
                    JSON_PRETTY_PRINT
                ));
                return self::SUCCESS;
            }

            // Display as table
            $rows = array_map(fn($voice) => [
                $voice->id,
                $voice->name,
                $voice->language,
                $voice->gender,
                $voice->description ?? '-',
            ], $voices);

            $this->table(
                ['ID', 'Name', 'Language', 'Gender', 'Description'],
                $rows
            );

            $this->newLine();
            $this->info(sprintf('Total: %d voice(s)', count($voices)));

            // Show default voice
            $defaultVoice = config('vibevoice.default_voice');
            $this->line("Default voice: <comment>{$defaultVoice}</comment>");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to fetch voices: {$e->getMessage()}");
            return self::FAILURE;
        }
    }
}

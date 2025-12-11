<?php

namespace BitsoftSol\VibeVoice\Commands;

use BitsoftSol\VibeVoice\Facades\VibeVoice;
use Illuminate\Console\Command;

class GenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'vibevoice:generate
                            {text : The text to convert to speech}
                            {--voice= : The voice ID to use}
                            {--output= : Output filename (without extension)}
                            {--async : Generate audio asynchronously via queue}';

    /**
     * The console command description.
     */
    protected $description = 'Generate audio from text using VibeVoice';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $text = $this->argument('text');
        $voice = $this->option('voice');
        $output = $this->option('output');
        $async = $this->option('async');

        if (empty(trim($text))) {
            $this->error('Text cannot be empty.');
            return self::FAILURE;
        }

        try {
            if ($async) {
                return $this->generateAsync($text, $voice, $output);
            }

            return $this->generateSync($text, $voice, $output);
        } catch (\Exception $e) {
            $this->error("Failed to generate audio: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    /**
     * Generate audio synchronously.
     */
    protected function generateSync(string $text, ?string $voice, ?string $output): int
    {
        $this->info('Generating audio...');

        $startTime = microtime(true);
        $path = VibeVoice::generateAndSave($text, $output, $voice);
        $endTime = microtime(true);

        $duration = round($endTime - $startTime, 2);

        $this->newLine();
        $this->info('Audio generated successfully!');
        $this->table(
            ['Property', 'Value'],
            [
                ['File', $path],
                ['Voice', $voice ?? config('vibevoice.default_voice')],
                ['Generation Time', "{$duration}s"],
            ]
        );

        return self::SUCCESS;
    }

    /**
     * Generate audio asynchronously.
     */
    protected function generateAsync(string $text, ?string $voice, ?string $output): int
    {
        VibeVoice::generateAsync($text, $voice, $output);

        $this->info('Audio generation job dispatched to queue.');
        $this->line('The audio will be generated in the background.');

        return self::SUCCESS;
    }
}

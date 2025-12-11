<?php

namespace BitsoftSol\VibeVoice\Commands;

use BitsoftSol\VibeVoice\Facades\VibeVoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'vibevoice:test
                            {--full : Run a full test including audio generation}';

    /**
     * The console command description.
     */
    protected $description = 'Test the connection to the VibeVoice API server';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing VibeVoice API connection...');
        $this->newLine();

        // Configuration check
        $this->testConfiguration();
        $this->newLine();

        // Health check
        if (!$this->testHealth()) {
            return self::FAILURE;
        }
        $this->newLine();

        // Voice list check
        if (!$this->testVoices()) {
            return self::FAILURE;
        }
        $this->newLine();

        // Full test with audio generation
        if ($this->option('full')) {
            if (!$this->testGeneration()) {
                return self::FAILURE;
            }
            $this->newLine();
        }

        // Storage check
        $this->testStorage();
        $this->newLine();

        $this->info('All tests passed successfully!');
        return self::SUCCESS;
    }

    /**
     * Test configuration settings.
     */
    protected function testConfiguration(): void
    {
        $this->components->task('Checking configuration', function () {
            return true;
        });

        $config = config('vibevoice');

        $this->table(
            ['Setting', 'Value'],
            [
                ['API URL', $config['api']['base_url']],
                ['API Key', $config['api']['key'] ? '***' . substr($config['api']['key'], -4) : 'Not set'],
                ['Timeout', $config['api']['timeout'] . 's'],
                ['Default Voice', $config['default_voice']],
                ['Storage Disk', $config['storage']['disk']],
                ['Storage Path', $config['storage']['path']],
            ]
        );
    }

    /**
     * Test API health endpoint.
     */
    protected function testHealth(): bool
    {
        $success = false;

        $this->components->task('Checking API health', function () use (&$success) {
            try {
                $health = VibeVoice::health();
                $success = ($health['status'] ?? '') === 'healthy';
                return $success;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Health check failed: {$e->getMessage()}");
                return false;
            }
        });

        if ($success) {
            $health = VibeVoice::health();
            if (!empty($health)) {
                $this->line('  API Response: ' . json_encode($health));
            }
        }

        return $success;
    }

    /**
     * Test fetching voices.
     */
    protected function testVoices(): bool
    {
        $voiceCount = 0;

        $this->components->task('Fetching available voices', function () use (&$voiceCount) {
            try {
                $voices = VibeVoice::voices();
                $voiceCount = count($voices);
                return $voiceCount > 0;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Failed to fetch voices: {$e->getMessage()}");
                return false;
            }
        });

        if ($voiceCount > 0) {
            $this->line("  Found {$voiceCount} voice(s) available");
        }

        return $voiceCount > 0;
    }

    /**
     * Test audio generation.
     */
    protected function testGeneration(): bool
    {
        $success = false;
        $path = null;

        $this->components->task('Testing audio generation', function () use (&$success, &$path) {
            try {
                $testText = 'Hello, this is a test of the VibeVoice integration.';
                $path = VibeVoice::generateAndSave($testText, 'vibevoice_test');
                $success = !empty($path);
                return $success;
            } catch (\Exception $e) {
                $this->newLine();
                $this->error("Generation failed: {$e->getMessage()}");
                return false;
            }
        });

        if ($success && $path) {
            $this->line("  Generated test file: {$path}");

            // Clean up test file
            $disk = config('vibevoice.storage.disk', 'public');
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
                $this->line('  Test file cleaned up');
            }
        }

        return $success;
    }

    /**
     * Test storage configuration.
     */
    protected function testStorage(): void
    {
        $disk = config('vibevoice.storage.disk', 'public');
        $path = config('vibevoice.storage.path', 'vibevoice');

        $this->components->task("Checking storage ({$disk}:{$path})", function () use ($disk, $path) {
            try {
                // Try to create a test file
                $testPath = "{$path}/.vibevoice_test";
                Storage::disk($disk)->put($testPath, 'test');
                Storage::disk($disk)->delete($testPath);
                return true;
            } catch (\Exception $e) {
                return false;
            }
        });
    }
}

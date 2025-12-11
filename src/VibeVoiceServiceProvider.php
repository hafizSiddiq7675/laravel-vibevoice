<?php

namespace BitsoftSol\VibeVoice;

use BitsoftSol\VibeVoice\Commands\GenerateCommand;
use BitsoftSol\VibeVoice\Commands\TestCommand;
use BitsoftSol\VibeVoice\Commands\VoicesCommand;
use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use Illuminate\Support\ServiceProvider;

class VibeVoiceServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/vibevoice.php',
            'vibevoice'
        );

        // Register the main client
        $this->app->singleton(VibeVoiceClientInterface::class, function ($app) {
            return new VibeVoiceClient($app['config']['vibevoice']);
        });

        // Register VibeVoiceManager as singleton
        $this->app->singleton(VibeVoiceManager::class, function ($app) {
            return new VibeVoiceManager(
                $app->make(VibeVoiceClientInterface::class),
                $app['config']['vibevoice']
            );
        });

        // Alias for the facade
        $this->app->alias(VibeVoiceManager::class, 'vibevoice');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/vibevoice.php' => config_path('vibevoice.php'),
            ], 'vibevoice-config');

            // Register Artisan commands
            $this->commands([
                GenerateCommand::class,
                VoicesCommand::class,
                TestCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            VibeVoiceClientInterface::class,
            VibeVoiceManager::class,
            'vibevoice',
        ];
    }
}

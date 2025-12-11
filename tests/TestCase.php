<?php

namespace BitsoftSol\VibeVoice\Tests;

use BitsoftSol\VibeVoice\VibeVoiceServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            VibeVoiceServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'VibeVoice' => \BitsoftSol\VibeVoice\Facades\VibeVoice::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('vibevoice.api.base_url', 'http://localhost:8000');
        $app['config']->set('vibevoice.api.key', 'test-api-key');
        $app['config']->set('vibevoice.api.timeout', 30);
        $app['config']->set('vibevoice.api.connect_timeout', 5);
        $app['config']->set('vibevoice.default_voice', 'en-US-female-1');
        $app['config']->set('vibevoice.storage.disk', 'local');
        $app['config']->set('vibevoice.storage.path', 'vibevoice-test');
        $app['config']->set('vibevoice.cache.enabled', false);
    }
}

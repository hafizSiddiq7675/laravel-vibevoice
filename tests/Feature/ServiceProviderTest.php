<?php

namespace BitsoftSol\VibeVoice\Tests\Feature;

use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use BitsoftSol\VibeVoice\Tests\TestCase;
use BitsoftSol\VibeVoice\VibeVoiceClient;
use BitsoftSol\VibeVoice\VibeVoiceManager;

class ServiceProviderTest extends TestCase
{
    public function test_it_registers_client_interface(): void
    {
        $client = $this->app->make(VibeVoiceClientInterface::class);

        $this->assertInstanceOf(VibeVoiceClient::class, $client);
    }

    public function test_it_registers_manager_as_singleton(): void
    {
        $manager1 = $this->app->make(VibeVoiceManager::class);
        $manager2 = $this->app->make(VibeVoiceManager::class);

        $this->assertSame($manager1, $manager2);
    }

    public function test_it_registers_vibevoice_alias(): void
    {
        $manager = $this->app->make('vibevoice');

        $this->assertInstanceOf(VibeVoiceManager::class, $manager);
    }

    public function test_it_merges_config(): void
    {
        $config = config('vibevoice');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('default_voice', $config);
        $this->assertArrayHasKey('audio', $config);
        $this->assertArrayHasKey('storage', $config);
        $this->assertArrayHasKey('queue', $config);
    }

    public function test_config_values_are_correct(): void
    {
        $this->assertEquals('http://localhost:8000', config('vibevoice.api.base_url'));
        $this->assertEquals('test-api-key', config('vibevoice.api.key'));
        $this->assertEquals('en-US-female-1', config('vibevoice.default_voice'));
        $this->assertEquals('local', config('vibevoice.storage.disk'));
    }
}

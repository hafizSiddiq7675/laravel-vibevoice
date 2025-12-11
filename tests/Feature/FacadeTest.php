<?php

namespace BitsoftSol\VibeVoice\Tests\Feature;

use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\DTOs\Voice;
use BitsoftSol\VibeVoice\Facades\VibeVoice;
use BitsoftSol\VibeVoice\Tests\TestCase;
use BitsoftSol\VibeVoice\VibeVoiceManager;
use Mockery;

class FacadeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_facade_resolves_to_manager(): void
    {
        $this->assertInstanceOf(VibeVoiceManager::class, VibeVoice::getFacadeRoot());
    }

    public function test_facade_can_call_generate(): void
    {
        $mockResult = new AudioResult(
            content: base64_encode('audio'),
            format: 'mp3',
            duration: 5.0,
            sampleRate: 24000,
        );

        $mockClient = Mockery::mock(VibeVoiceClientInterface::class);
        $mockClient->shouldReceive('generate')
            ->with('Test text', null)
            ->once()
            ->andReturn($mockResult);

        $this->app->instance(VibeVoiceClientInterface::class, $mockClient);

        $result = VibeVoice::generate('Test text');

        $this->assertInstanceOf(AudioResult::class, $result);
        $this->assertEquals(5.0, $result->duration);
    }

    public function test_facade_can_call_voices(): void
    {
        $mockVoices = [
            new Voice('v1', 'Voice 1', 'en-US', 'female'),
            new Voice('v2', 'Voice 2', 'en-GB', 'male'),
        ];

        $mockClient = Mockery::mock(VibeVoiceClientInterface::class);
        $mockClient->shouldReceive('voices')
            ->once()
            ->andReturn($mockVoices);

        $this->app->instance(VibeVoiceClientInterface::class, $mockClient);

        $voices = VibeVoice::voices();

        $this->assertCount(2, $voices);
        $this->assertEquals('Voice 1', $voices[0]->name);
    }

    public function test_facade_can_check_health(): void
    {
        $mockClient = Mockery::mock(VibeVoiceClientInterface::class);
        $mockClient->shouldReceive('isHealthy')
            ->once()
            ->andReturn(true);

        $this->app->instance(VibeVoiceClientInterface::class, $mockClient);

        $this->assertTrue(VibeVoice::isHealthy());
    }

    public function test_facade_returns_config(): void
    {
        $config = VibeVoice::config();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('api', $config);
        $this->assertArrayHasKey('default_voice', $config);
    }
}

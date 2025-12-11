<?php

namespace BitsoftSol\VibeVoice\Tests\Feature;

use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\DTOs\Voice;
use BitsoftSol\VibeVoice\Exceptions\GenerationException;
use BitsoftSol\VibeVoice\Tests\TestCase;
use BitsoftSol\VibeVoice\VibeVoiceClient;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Mockery;

class VibeVoiceClientTest extends TestCase
{
    protected function createMockClient(array $responses): VibeVoiceClient
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new Client(['handler' => $handlerStack]);

        $config = config('vibevoice');
        $client = new VibeVoiceClient($config);

        // Use reflection to replace the HTTP client
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('http');
        $property->setAccessible(true);
        $property->setValue($client, $httpClient);

        return $client;
    }

    public function test_it_can_generate_audio(): void
    {
        $responseData = [
            'audio' => base64_encode('fake audio content'),
            'format' => 'mp3',
            'duration' => 5.5,
            'sample_rate' => 24000,
            'voice' => 'en-US-female-1',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($responseData)),
        ]);

        $result = $client->generate('Hello, world!', 'en-US-female-1');

        $this->assertInstanceOf(AudioResult::class, $result);
        $this->assertEquals('mp3', $result->format);
        $this->assertEquals(5.5, $result->duration);
        $this->assertEquals('en-US-female-1', $result->voice);
    }

    public function test_it_throws_exception_for_empty_text(): void
    {
        $client = $this->createMockClient([]);

        $this->expectException(GenerationException::class);
        $this->expectExceptionMessage('empty text');

        $client->generate('   ');
    }

    public function test_it_can_generate_conversation(): void
    {
        $responseData = [
            'audio' => base64_encode('conversation audio'),
            'format' => 'mp3',
            'duration' => 30.0,
            'sample_rate' => 24000,
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($responseData)),
        ]);

        $lines = [
            ['text' => 'Hello!', 'voice' => 'voice-1'],
            ['text' => 'Hi there!', 'voice' => 'voice-2'],
        ];

        $result = $client->conversation($lines);

        $this->assertInstanceOf(AudioResult::class, $result);
        $this->assertEquals(30.0, $result->duration);
    }

    public function test_it_throws_exception_for_too_many_speakers(): void
    {
        $client = $this->createMockClient([]);

        $lines = [
            ['text' => 'Line 1', 'voice' => 'voice-1'],
            ['text' => 'Line 2', 'voice' => 'voice-2'],
            ['text' => 'Line 3', 'voice' => 'voice-3'],
            ['text' => 'Line 4', 'voice' => 'voice-4'],
            ['text' => 'Line 5', 'voice' => 'voice-5'],
        ];

        $this->expectException(GenerationException::class);
        $this->expectExceptionMessage('Too many speakers');

        $client->conversation($lines);
    }

    public function test_it_can_fetch_voices(): void
    {
        $responseData = [
            'voices' => [
                ['id' => 'voice-1', 'name' => 'Voice One', 'language' => 'en-US', 'gender' => 'female'],
                ['id' => 'voice-2', 'name' => 'Voice Two', 'language' => 'en-GB', 'gender' => 'male'],
            ],
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($responseData)),
        ]);

        $voices = $client->voices();

        $this->assertCount(2, $voices);
        $this->assertInstanceOf(Voice::class, $voices[0]);
        $this->assertEquals('voice-1', $voices[0]->id);
        $this->assertEquals('Voice Two', $voices[1]->name);
    }

    public function test_it_can_check_health(): void
    {
        $responseData = [
            'status' => 'healthy',
            'model' => 'VibeVoice-1.5B',
            'gpu' => 'NVIDIA RTX 4090',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($responseData)),
        ]);

        $this->assertTrue($client->isHealthy());
    }

    public function test_it_returns_false_for_unhealthy_status(): void
    {
        $responseData = [
            'status' => 'unhealthy',
            'error' => 'Model not loaded',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($responseData)),
        ]);

        $this->assertFalse($client->isHealthy());
    }

    public function test_it_returns_health_details(): void
    {
        $responseData = [
            'status' => 'healthy',
            'model' => 'VibeVoice-1.5B',
            'gpu' => 'NVIDIA RTX 4090',
            'memory_used' => '8GB',
        ];

        $client = $this->createMockClient([
            new Response(200, [], json_encode($responseData)),
        ]);

        $health = $client->health();

        $this->assertEquals('healthy', $health['status']);
        $this->assertEquals('VibeVoice-1.5B', $health['model']);
    }
}

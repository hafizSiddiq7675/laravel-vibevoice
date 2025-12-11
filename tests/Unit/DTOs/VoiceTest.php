<?php

namespace BitsoftSol\VibeVoice\Tests\Unit\DTOs;

use BitsoftSol\VibeVoice\DTOs\Voice;
use BitsoftSol\VibeVoice\Tests\TestCase;

class VoiceTest extends TestCase
{
    public function test_it_can_be_created_from_array(): void
    {
        $data = [
            'id' => 'en-US-female-1',
            'name' => 'Sarah',
            'language' => 'en-US',
            'gender' => 'female',
            'description' => 'A friendly female voice',
            'preview_url' => 'https://example.com/preview.mp3',
            'metadata' => ['style' => 'conversational'],
        ];

        $voice = Voice::fromArray($data);

        $this->assertEquals('en-US-female-1', $voice->id);
        $this->assertEquals('Sarah', $voice->name);
        $this->assertEquals('en-US', $voice->language);
        $this->assertEquals('female', $voice->gender);
        $this->assertEquals('A friendly female voice', $voice->description);
        $this->assertEquals('https://example.com/preview.mp3', $voice->preview_url);
        $this->assertEquals(['style' => 'conversational'], $voice->metadata);
    }

    public function test_it_handles_minimal_data(): void
    {
        $data = [
            'id' => 'voice-1',
            'name' => 'Voice One',
        ];

        $voice = Voice::fromArray($data);

        $this->assertEquals('voice-1', $voice->id);
        $this->assertEquals('Voice One', $voice->name);
        $this->assertEquals('en-US', $voice->language); // default
        $this->assertEquals('neutral', $voice->gender); // default
        $this->assertNull($voice->description);
        $this->assertNull($voice->preview_url);
        $this->assertEquals([], $voice->metadata);
    }

    public function test_it_can_be_converted_to_array(): void
    {
        $voice = new Voice(
            id: 'test-id',
            name: 'Test Voice',
            language: 'en-GB',
            gender: 'male',
            description: 'A test voice',
        );

        $array = $voice->toArray();

        $this->assertEquals('test-id', $array['id']);
        $this->assertEquals('Test Voice', $array['name']);
        $this->assertEquals('en-GB', $array['language']);
        $this->assertEquals('male', $array['gender']);
        $this->assertEquals('A test voice', $array['description']);
    }

    public function test_it_is_json_serializable(): void
    {
        $voice = new Voice(
            id: 'json-test',
            name: 'JSON Test',
            language: 'en-US',
            gender: 'female',
        );

        $json = json_encode($voice);
        $decoded = json_decode($json, true);

        $this->assertEquals('json-test', $decoded['id']);
        $this->assertEquals('JSON Test', $decoded['name']);
    }
}

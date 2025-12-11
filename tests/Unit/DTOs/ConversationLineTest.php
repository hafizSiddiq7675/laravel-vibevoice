<?php

namespace BitsoftSol\VibeVoice\Tests\Unit\DTOs;

use BitsoftSol\VibeVoice\DTOs\ConversationLine;
use BitsoftSol\VibeVoice\Tests\TestCase;

class ConversationLineTest extends TestCase
{
    public function test_it_can_be_created_from_array(): void
    {
        $data = [
            'text' => 'Hello, how are you?',
            'voice' => 'en-US-female-1',
            'speaker' => 'Alice',
            'options' => ['speed' => 1.0],
        ];

        $line = ConversationLine::fromArray($data);

        $this->assertEquals('Hello, how are you?', $line->text);
        $this->assertEquals('en-US-female-1', $line->voice);
        $this->assertEquals('Alice', $line->speaker);
        $this->assertEquals(['speed' => 1.0], $line->options);
    }

    public function test_it_handles_minimal_data(): void
    {
        $data = [
            'text' => 'Simple text',
            'voice' => 'voice-1',
        ];

        $line = ConversationLine::fromArray($data);

        $this->assertEquals('Simple text', $line->text);
        $this->assertEquals('voice-1', $line->voice);
        $this->assertNull($line->speaker);
        $this->assertEquals([], $line->options);
    }

    public function test_it_can_create_multiple_from_array(): void
    {
        $data = [
            ['text' => 'Line 1', 'voice' => 'voice-1'],
            ['text' => 'Line 2', 'voice' => 'voice-2'],
            ['text' => 'Line 3', 'voice' => 'voice-1'],
        ];

        $lines = ConversationLine::fromArrayMultiple($data);

        $this->assertCount(3, $lines);
        $this->assertInstanceOf(ConversationLine::class, $lines[0]);
        $this->assertInstanceOf(ConversationLine::class, $lines[1]);
        $this->assertInstanceOf(ConversationLine::class, $lines[2]);
        $this->assertEquals('Line 1', $lines[0]->text);
        $this->assertEquals('voice-2', $lines[1]->voice);
    }

    public function test_it_can_be_converted_to_array(): void
    {
        $line = new ConversationLine(
            text: 'Test text',
            voice: 'test-voice',
            speaker: 'Bob',
            options: ['pitch' => 1.2],
        );

        $array = $line->toArray();

        $this->assertEquals('Test text', $array['text']);
        $this->assertEquals('test-voice', $array['voice']);
        $this->assertEquals('Bob', $array['speaker']);
        $this->assertEquals(['pitch' => 1.2], $array['options']);
    }

    public function test_it_is_json_serializable(): void
    {
        $line = new ConversationLine(
            text: 'JSON test',
            voice: 'json-voice',
        );

        $json = json_encode($line);
        $decoded = json_decode($json, true);

        $this->assertEquals('JSON test', $decoded['text']);
        $this->assertEquals('json-voice', $decoded['voice']);
    }
}

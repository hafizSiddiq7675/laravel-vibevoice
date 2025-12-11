<?php

namespace BitsoftSol\VibeVoice\Tests\Unit\DTOs;

use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\Tests\TestCase;

class AudioResultTest extends TestCase
{
    public function test_it_can_be_created_from_array(): void
    {
        $data = [
            'audio' => base64_encode('fake audio content'),
            'format' => 'mp3',
            'duration' => 12.5,
            'sample_rate' => 24000,
            'voice' => 'en-US-female-1',
            'metadata' => ['chars' => 100],
        ];

        $result = AudioResult::fromArray($data);

        $this->assertEquals($data['audio'], $result->content);
        $this->assertEquals('mp3', $result->format);
        $this->assertEquals(12.5, $result->duration);
        $this->assertEquals(24000, $result->sampleRate);
        $this->assertEquals('en-US-female-1', $result->voice);
        $this->assertEquals(['chars' => 100], $result->metadata);
    }

    public function test_it_handles_content_key(): void
    {
        $data = [
            'content' => 'audio-data',
            'format' => 'wav',
            'duration' => 5.0,
        ];

        $result = AudioResult::fromArray($data);

        $this->assertEquals('audio-data', $result->content);
    }

    public function test_it_can_detect_base64_encoding(): void
    {
        $originalContent = 'fake audio content';
        $base64Content = base64_encode($originalContent);

        $result = new AudioResult(
            content: $base64Content,
            format: 'mp3',
            duration: 1.0,
            sampleRate: 24000,
        );

        $this->assertTrue($result->isBase64Encoded());
        $this->assertEquals($originalContent, $result->getAudioContent());
    }

    public function test_it_returns_raw_content_when_not_base64(): void
    {
        $content = 'not base64 content!@#$';

        $result = new AudioResult(
            content: $content,
            format: 'mp3',
            duration: 1.0,
            sampleRate: 24000,
        );

        $this->assertFalse($result->isBase64Encoded());
        $this->assertEquals($content, $result->getAudioContent());
    }

    public function test_it_formats_duration_correctly(): void
    {
        $result = new AudioResult(
            content: '',
            format: 'mp3',
            duration: 125.5,
            sampleRate: 24000,
        );

        $this->assertEquals('02:05.50', $result->getFormattedDuration());
    }

    public function test_it_can_be_converted_to_array(): void
    {
        $result = new AudioResult(
            content: 'test-content',
            format: 'mp3',
            duration: 10.0,
            sampleRate: 22050,
            voice: 'test-voice',
        );

        $array = $result->toArray();

        $this->assertEquals('test-content', $array['content']);
        $this->assertEquals('mp3', $array['format']);
        $this->assertEquals(10.0, $array['duration']);
        $this->assertEquals(22050, $array['sample_rate']);
        $this->assertEquals('test-voice', $array['voice']);
    }

    public function test_it_is_json_serializable(): void
    {
        $result = new AudioResult(
            content: 'json-content',
            format: 'wav',
            duration: 5.5,
            sampleRate: 24000,
        );

        $json = json_encode($result);
        $decoded = json_decode($json, true);

        $this->assertEquals('json-content', $decoded['content']);
        $this->assertEquals('wav', $decoded['format']);
    }
}

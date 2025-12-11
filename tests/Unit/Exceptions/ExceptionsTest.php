<?php

namespace BitsoftSol\VibeVoice\Tests\Unit\Exceptions;

use BitsoftSol\VibeVoice\Exceptions\AuthenticationException;
use BitsoftSol\VibeVoice\Exceptions\ConnectionException;
use BitsoftSol\VibeVoice\Exceptions\GenerationException;
use BitsoftSol\VibeVoice\Exceptions\RateLimitException;
use BitsoftSol\VibeVoice\Exceptions\VibeVoiceException;
use BitsoftSol\VibeVoice\Tests\TestCase;

class ExceptionsTest extends TestCase
{
    public function test_vibevoice_exception_includes_context(): void
    {
        $exception = VibeVoiceException::withContext(
            'Test error',
            ['key' => 'value'],
            500
        );

        $this->assertEquals('Test error', $exception->getMessage());
        $this->assertEquals(500, $exception->getCode());
        $this->assertEquals(['key' => 'value'], $exception->getContext());
    }

    public function test_connection_timeout_exception(): void
    {
        $exception = ConnectionException::timeout('http://localhost:8000', 30);

        $this->assertStringContainsString('timed out', $exception->getMessage());
        $this->assertStringContainsString('30', $exception->getMessage());
        $this->assertEquals(408, $exception->getCode());
        $this->assertEquals('http://localhost:8000', $exception->getContext()['url']);
    }

    public function test_connection_refused_exception(): void
    {
        $exception = ConnectionException::refused('http://localhost:8000');

        $this->assertStringContainsString('refused', $exception->getMessage());
        $this->assertEquals(503, $exception->getCode());
    }

    public function test_connection_failed_exception(): void
    {
        $exception = ConnectionException::failed('http://localhost:8000', 'Network error');

        $this->assertStringContainsString('Failed to connect', $exception->getMessage());
        $this->assertStringContainsString('Network error', $exception->getMessage());
    }

    public function test_authentication_invalid_api_key(): void
    {
        $exception = AuthenticationException::invalidApiKey();

        $this->assertStringContainsString('Invalid', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function test_authentication_missing_api_key(): void
    {
        $exception = AuthenticationException::missingApiKey();

        $this->assertStringContainsString('No API key', $exception->getMessage());
        $this->assertEquals(401, $exception->getCode());
    }

    public function test_rate_limit_exceeded(): void
    {
        $exception = RateLimitException::exceeded(60);

        $this->assertStringContainsString('Rate limit exceeded', $exception->getMessage());
        $this->assertStringContainsString('60', $exception->getMessage());
        $this->assertEquals(429, $exception->getCode());
        $this->assertEquals(60, $exception->getRetryAfter());
    }

    public function test_rate_limit_without_retry_after(): void
    {
        $exception = RateLimitException::exceeded();

        $this->assertNull($exception->getRetryAfter());
    }

    public function test_generation_invalid_voice(): void
    {
        $exception = GenerationException::invalidVoice('nonexistent-voice');

        $this->assertStringContainsString('nonexistent-voice', $exception->getMessage());
        $this->assertStringContainsString('not available', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_generation_empty_text(): void
    {
        $exception = GenerationException::emptyText();

        $this->assertStringContainsString('empty text', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_generation_text_too_long(): void
    {
        $exception = GenerationException::textTooLong(10000, 5000);

        $this->assertStringContainsString('10000', $exception->getMessage());
        $this->assertStringContainsString('5000', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }

    public function test_generation_too_many_speakers(): void
    {
        $exception = GenerationException::tooManySpeakers(6, 4);

        $this->assertStringContainsString('Too many speakers', $exception->getMessage());
        $this->assertStringContainsString('6', $exception->getMessage());
        $this->assertEquals(400, $exception->getCode());
    }
}

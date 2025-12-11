<?php

namespace BitsoftSol\VibeVoice;

use BitsoftSol\VibeVoice\Contracts\VibeVoiceClientInterface;
use BitsoftSol\VibeVoice\DTOs\AudioResult;
use BitsoftSol\VibeVoice\DTOs\ConversationLine;
use BitsoftSol\VibeVoice\DTOs\Voice;
use BitsoftSol\VibeVoice\Exceptions\AuthenticationException;
use BitsoftSol\VibeVoice\Exceptions\ConnectionException;
use BitsoftSol\VibeVoice\Exceptions\GenerationException;
use BitsoftSol\VibeVoice\Exceptions\RateLimitException;
use BitsoftSol\VibeVoice\Exceptions\VibeVoiceException;
use Generator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Cache;

class VibeVoiceClient implements VibeVoiceClientInterface
{
    protected Client $http;
    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->http = new Client([
            'base_uri' => rtrim($config['api']['base_url'], '/') . '/',
            'timeout' => $config['api']['timeout'],
            'connect_timeout' => $config['api']['connect_timeout'],
            'headers' => $this->buildHeaders(),
        ]);
    }

    /**
     * Generate audio from text using a single voice.
     */
    public function generate(string $text, ?string $voice = null): AudioResult
    {
        if (empty(trim($text))) {
            throw GenerationException::emptyText();
        }

        $voice = $voice ?? $this->config['default_voice'];

        $response = $this->request('POST', 'api/generate', [
            'json' => [
                'text' => $text,
                'voice' => $voice,
                'format' => $this->config['audio']['format'],
                'sample_rate' => $this->config['audio']['sample_rate'],
            ],
        ]);

        return AudioResult::fromArray($response);
    }

    /**
     * Generate audio from a multi-speaker conversation.
     */
    public function conversation(array $lines): AudioResult
    {
        if (empty($lines)) {
            throw GenerationException::emptyText();
        }

        // Validate max speakers (4)
        $uniqueVoices = collect($lines)->pluck('voice')->unique()->count();
        if ($uniqueVoices > 4) {
            throw GenerationException::tooManySpeakers($uniqueVoices);
        }

        // Convert to ConversationLine DTOs if needed
        $formattedLines = array_map(function ($line) {
            if ($line instanceof ConversationLine) {
                return $line->toArray();
            }
            return $line;
        }, $lines);

        $response = $this->request('POST', 'api/generate/conversation', [
            'json' => [
                'lines' => $formattedLines,
                'format' => $this->config['audio']['format'],
                'sample_rate' => $this->config['audio']['sample_rate'],
            ],
        ]);

        return AudioResult::fromArray($response);
    }

    /**
     * Stream audio generation in real-time.
     */
    public function stream(string $text, ?string $voice = null): Generator
    {
        if (empty(trim($text))) {
            throw GenerationException::emptyText();
        }

        $voice = $voice ?? $this->config['default_voice'];

        $response = $this->http->request('POST', 'api/stream', [
            'json' => [
                'text' => $text,
                'voice' => $voice,
                'format' => $this->config['audio']['format'],
            ],
            'stream' => true,
        ]);

        $body = $response->getBody();
        $chunkSize = $this->config['streaming']['chunk_size'];

        while (!$body->eof()) {
            $chunk = $body->read($chunkSize);
            if ($chunk !== '') {
                yield $chunk;
            }
        }
    }

    /**
     * Get list of available voices.
     */
    public function voices(): array
    {
        $cacheKey = $this->config['cache']['prefix'] . ':voices';

        if ($this->config['cache']['enabled']) {
            $cached = Cache::get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        $response = $this->request('GET', 'api/voices');

        $voices = array_map(
            fn($voice) => Voice::fromArray($voice),
            $response['voices'] ?? $response
        );

        if ($this->config['cache']['enabled']) {
            Cache::put($cacheKey, $voices, $this->config['cache']['ttl']);
        }

        return $voices;
    }

    /**
     * Check API server health.
     */
    public function isHealthy(): bool
    {
        try {
            $health = $this->health();
            return ($health['status'] ?? '') === 'healthy';
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get API server health details.
     */
    public function health(): array
    {
        return $this->request('GET', 'api/health');
    }

    /**
     * Make an HTTP request to the API.
     */
    protected function request(string $method, string $uri, array $options = []): array
    {
        try {
            $response = $this->http->request($method, $uri, $options);
            $body = (string) $response->getBody();

            return json_decode($body, true) ?? [];
        } catch (ConnectException $e) {
            $this->handleConnectException($e);
        } catch (RequestException $e) {
            $this->handleRequestException($e);
        }
    }

    /**
     * Handle connection exceptions.
     */
    protected function handleConnectException(ConnectException $e): never
    {
        $message = $e->getMessage();

        if (str_contains($message, 'timed out')) {
            throw ConnectionException::timeout(
                $this->config['api']['base_url'],
                $this->config['api']['connect_timeout']
            );
        }

        if (str_contains($message, 'Connection refused')) {
            throw ConnectionException::refused($this->config['api']['base_url']);
        }

        throw ConnectionException::failed($this->config['api']['base_url'], $message);
    }

    /**
     * Handle request exceptions.
     */
    protected function handleRequestException(RequestException $e): never
    {
        $response = $e->getResponse();

        if (!$response) {
            throw ConnectionException::failed(
                $this->config['api']['base_url'],
                $e->getMessage()
            );
        }

        $statusCode = $response->getStatusCode();
        $body = json_decode((string) $response->getBody(), true) ?? [];
        $message = $body['message'] ?? $body['detail'] ?? $e->getMessage();

        match ($statusCode) {
            401 => throw AuthenticationException::invalidApiKey(),
            429 => throw RateLimitException::exceeded($body['retry_after'] ?? null),
            400 => throw GenerationException::failed($message),
            default => throw new VibeVoiceException($message, $statusCode),
        };
    }

    /**
     * Build request headers.
     */
    protected function buildHeaders(): array
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (!empty($this->config['api']['key'])) {
            $headers['Authorization'] = 'Bearer ' . $this->config['api']['key'];
        }

        return $headers;
    }
}

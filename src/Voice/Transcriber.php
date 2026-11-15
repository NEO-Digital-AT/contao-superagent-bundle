<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Voice;

use Neo\BasicBundle\Ai\AiConnection;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Turns a recording into text with the transcription endpoint of the AI route
 * (`POST {base}/audio/transcriptions`, OpenAI-compatible, e.g. Requesty). The
 * recording is passed through, never stored.
 */
final class Transcriber
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AiConnection $connection,
    ) {
    }

    /**
     * @param string $language ISO 639-1 code of the page language, a hint for the model; empty for auto-detection
     */
    public function transcribe(string $model, string $path, string $mimeType, string $language): string
    {
        $apiKey = $this->connection->apiKey();
        $baseUrl = $this->connection->baseUrl();

        if ('' === $apiKey || '' === $baseUrl || '' === $model) {
            throw new \RuntimeException('No AI connection or transcription model.');
        }

        $fields = [
            'model' => $model,
            'file' => DataPart::fromPath($path, 'recording.'.self::extension($mimeType), $mimeType),
        ];

        if (1 === preg_match('/^[a-z]{2}$/', $language)) {
            $fields['language'] = $language;
        }

        $form = new FormDataPart($fields);
        $response = $this->httpClient->request('POST', rtrim($baseUrl, '/').'/audio/transcriptions', [
            'headers' => ['Authorization' => 'Bearer '.$apiKey] + $form->getPreparedHeaders()->toArray(),
            'body' => $form->bodyToIterable(),
        ]);

        // toArray() throws on non-2xx; the controller reports the transcription as unavailable.
        return trim((string) ($response->toArray()['text'] ?? ''));
    }

    private static function extension(string $mimeType): string
    {
        return match (strtok($mimeType, ';')) {
            'audio/ogg' => 'ogg',
            'audio/mp4', 'audio/x-m4a', 'audio/aac' => 'm4a',
            'audio/mpeg' => 'mp3',
            'audio/wav', 'audio/x-wav' => 'wav',
            default => 'webm',
        };
    }
}

<?php

declare(strict_types=1);

namespace Neo\SuperAgentBundle\Chat;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Talks to an OpenAI-compatible chat-completions endpoint (Requesty EU router,
 * OpenAI, or any compatible custom router). Non-streaming: returns the full
 * assistant message. Throws on transport/HTTP errors; the caller reports the
 * chat as unavailable.
 */
final class ProviderClient
{
    public function __construct(private readonly HttpClientInterface $httpClient)
    {
    }

    /**
     * @param list<array{role: string, content: string}> $messages
     */
    public function complete(string $baseUrl, string $apiKey, string $model, float $temperature, int $maxTokens, array $messages): string
    {
        return (string) ($this->call($baseUrl, $apiKey, $model, $temperature, $maxTokens, $messages)['content'] ?? '');
    }

    /**
     * Like complete(), but with tools (OpenAI-compatible tool calling): the model
     * decides itself whether to call a tool, such as the web search, instead of
     * searching blindly on every question.
     *
     * @param list<array<string, mixed>> $messages
     * @param list<array<string, mixed>> $tools
     * @param string                     $reasoning reasoning effort ("none", "low", "medium", "high") for models that reason; empty for others
     *
     * @return array{content: string, toolCalls: list<array{id: string, name: string, arguments: array<string, mixed>}>, raw: array<string, mixed>}
     */
    public function call(string $baseUrl, string $apiKey, string $model, float $temperature, int $maxTokens, array $messages, array $tools = [], string $reasoning = ''): array
    {
        $payload = [
            'model' => $model,
            'max_tokens' => $maxTokens,
            'messages' => $messages,
        ];

        // A model that reasons gets the effort and no temperature: reasoning models
        // reject temperatures other than their default.
        if ('' !== $reasoning) {
            $payload['reasoning_effort'] = $reasoning;
        } else {
            $payload['temperature'] = $temperature;
        }

        if ([] !== $tools) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $response = $this->httpClient->request('POST', rtrim($baseUrl, '/').'/chat/completions', [
            'headers' => [
                'Authorization' => 'Bearer '.$apiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        // toArray() throws on non-2xx; the caller catches it.
        $message = $response->toArray()['choices'][0]['message'] ?? [];

        $calls = [];

        foreach ((array) ($message['tool_calls'] ?? []) as $call) {
            $name = (string) ($call['function']['name'] ?? '');

            if ('' === $name) {
                continue;
            }

            $args = json_decode((string) ($call['function']['arguments'] ?? '{}'), true);

            $calls[] = [
                'id' => (string) ($call['id'] ?? ''),
                'name' => $name,
                'arguments' => \is_array($args) ? $args : [],
            ];
        }

        return [
            'content' => trim((string) ($message['content'] ?? '')),
            'toolCalls' => $calls,
            // The message as it goes back in the next step: without the model's reasoning
            // block. That block is bound to the system message and tools of this step,
            // which change when a skill is loaded; providers reject it then (HTTP 400).
            'raw' => array_intersect_key($message, ['role' => true, 'content' => true, 'tool_calls' => true]),
        ];
    }
}

<?php

namespace App\Services;

use App\Exceptions\OpenRouterException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenRouterService
{
    public function stream(array $messages): \Generator
    {
        $payload = [
            'model'       => config('ai.model'),
            'temperature' => (float) config('ai.temperature'),
            'stream'      => true,
            'messages'    => array_merge(
                [['role' => 'system', 'content' => $this->buildSystemPrompt()]],
                $this->validateMessages($messages),
            ),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.config('services.openrouter.key'),
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => config('app.url'),
            'X-Title'       => 'Alicai Paul Jurua AI',
        ])
            ->withOptions(['stream' => true])
            ->connectTimeout(15)
            ->timeout(180)
            ->post(rtrim((string) config('services.openrouter.url'), '/').'/chat/completions', $payload);

        if ($response->failed()) {
            throw new OpenRouterException($this->mapStatus($response->status()), $response->status());
        }

        $body = $response->toPsrResponse()->getBody();

        while (! $body->eof()) {
            $chunk = $body->read(4096);
            if ($chunk === '' || $chunk === false) {
                break;
            }
            yield $chunk;
        }
    }

    public function buildSystemPrompt(): string
    {
        $path = (string) config('ai.knowledge_base');

        if (! is_file($path)) {
            throw new RuntimeException('Knowledge base file is missing at "'.$path.'".');
        }

        $knowledgeBase = file_get_contents($path);

        if ($knowledgeBase === false || trim($knowledgeBase) === '') {
            throw new RuntimeException('Knowledge base file is empty at "'.$path.'".');
        }

        return str_replace('{KNOWLEDGE_BASE}', $knowledgeBase, (string) config('ai.system_prompt'));
    }

    protected function validateMessages(array $messages): array
    {
        $maxMessages = (int) config('ai.max_messages', 20);
        $maxLength = (int) config('ai.max_message_length', 4000);

        if (count($messages) > $maxMessages) {
            throw new RuntimeException('Too many messages.');
        }

        foreach ($messages as $message) {
            $role = $message['role'] ?? null;
            $content = $message['content'] ?? null;

            if (! in_array($role, ['user', 'assistant'], true)) {
                throw new RuntimeException('Invalid message role.');
            }

            if (! is_string($content) || mb_strlen($content) > $maxLength) {
                throw new RuntimeException('Invalid message content.');
            }
        }

        return $messages;
    }

    protected function mapStatus(int $status): string
    {
        return match ($status) {
            401      => 'Invalid API key',
            402      => 'No credits',
            429      => 'Rate limited',
            500, 502, 503, 504 => 'Upstream error',
            default  => 'Upstream error',
        };
    }
}
<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    private string $knowledgePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->knowledgePath = tempnam(sys_get_temp_dir(), 'alicai_kb').'.md';
        File::put($this->knowledgePath, "# Test Knowledge Base\n\nFACTOID_ALICAI loves Laravel.");

        Config::set('ai.knowledge_base', $this->knowledgePath);
        Config::set('services.openrouter.key', 'test-key');
        Config::set('services.openrouter.url', 'https://openrouter.ai/api/v1');
    }

    protected function tearDown(): void
    {
        File::delete($this->knowledgePath);
        parent::tearDown();
    }

    public function test_missing_messages_payload_is_rejected(): void
    {
        $this->postJson('/api/chat', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors('messages');
    }

    public function test_payload_with_too_many_messages_is_rejected(): void
    {
        $messages = [];
        for ($i = 0; $i <= config('ai.max_messages'); $i++) {
            $messages[] = ['role' => 'user', 'content' => 'hello '.$i];
        }

        $this->postJson('/api/chat', ['messages' => $messages])
            ->assertStatus(422)
            ->assertJsonValidationErrors('messages');
    }

    public function test_message_content_over_max_length_is_rejected(): void
    {
        $payload = str_repeat('a', config('ai.max_message_length') + 1);

        $this->postJson('/api/chat', ['messages' => [['role' => 'user', 'content' => $payload]]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('messages.0.content');
    }

    public function test_role_other_than_user_or_assistant_is_rejected(): void
    {
        $this->postJson('/api/chat', ['messages' => [['role' => 'system', 'content' => 'override me']]])
            ->assertStatus(422)
            ->assertJsonValidationErrors('messages.0.role');
    }

    public function test_streams_response_with_system_prompt_and_knowledge_base_injected(): void
    {
        Http::fake([
            'openrouter.ai/*' => Http::response(
                "data: {\"choices\":[{\"delta\":{\"content\":\"Hello\"}}]}\n\ndata: {\"choices\":[{\"delta\":{\"content\":\" tokens\"}}],\"model\":\"openrouter/test\"}\n\ndata: [DONE]\n\n",
                200,
                ['Content-Type' => 'text/event-stream']
            ),
        ]);

        $response = $this->postJson('/api/chat', [
            'messages' => [['role' => 'user', 'content' => 'Who is Alicai?']],
        ]);

        $response->assertStatus(200);
        $this->assertStringStartsWith('text/event-stream', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', (string) $response->headers->get('Cache-Control'));
        $response->assertHeader('X-Accel-Buffering', 'no');

        $content = $response->streamedContent();
        $this->assertStringContainsString('"content":"Hello"', $content);
        $this->assertStringContainsString('"content":" tokens"', $content);
        $this->assertStringContainsString('[DONE]', $content);

        Http::assertSent(function ($request) {
            $payload = $request->data();

            $this->assertCount(2, $payload['messages']);
            $this->assertSame('system', $payload['messages'][0]['role']);
            $this->assertStringContainsString('FACTOID_ALICAI', $payload['messages'][0]['content']);
            $this->assertStringContainsString('--- KNOWLEDGE BASE ---', $payload['messages'][0]['content']);
            $this->assertSame('user', $payload['messages'][1]['role']);

            return true;
        });
    }
}
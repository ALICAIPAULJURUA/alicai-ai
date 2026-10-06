<?php

namespace Tests\Unit;

use App\Services\OpenRouterService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Tests\TestCase;

class OpenRouterServiceTest extends TestCase
{
    private array $tempFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->tempFiles as $file) {
            File::delete($file);
        }
        parent::tearDown();
    }

    private function writeKnowledgeBase(string $content): string
    {
        $path = tempnam(sys_get_temp_dir(), 'alicai_kb').'.md';
        File::put($path, $content);
        $this->tempFiles[] = $path;

        return $path;
    }

    public function test_missing_knowledge_base_file_throws_runtime_exception(): void
    {
        Config::set('ai.knowledge_base', sys_get_temp_dir().'/alicai_kb_missing_'.uniqid().'.md');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('missing');

        (new OpenRouterService)->buildSystemPrompt();
    }

    public function test_empty_knowledge_base_file_throws_runtime_exception(): void
    {
        Config::set('ai.knowledge_base', $this->writeKnowledgeBase('   '));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('empty');

        (new OpenRouterService)->buildSystemPrompt();
    }

    public function test_valid_knowledge_base_is_injected_into_system_prompt(): void
    {
        $kb = "# Alicai Paul Jurua\n\nUNIQUE_FACTOID_XYZ about Alicai.";
        Config::set('ai.knowledge_base', $this->writeKnowledgeBase($kb));

        $prompt = (new OpenRouterService)->buildSystemPrompt();

        $this->assertStringContainsString($kb, $prompt);
        $this->assertStringContainsString('UNIQUE_FACTOID_XYZ', $prompt);
        $this->assertStringContainsString('--- KNOWLEDGE BASE ---', $prompt);
        $this->assertStringNotContainsString('{KNOWLEDGE_BASE}', $prompt);
    }
}
<?php

namespace App\Http\Controllers;

use App\Exceptions\OpenRouterException;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends Controller
{
    public function __construct(private readonly OpenRouterService $openRouter)
    {
    }

    public function stream(Request $request): StreamedResponse
    {
        $data = $request->validate([
            'messages'           => ['required', 'array', 'min:1', 'max:'.config('ai.max_messages')],
            'messages.*.role'    => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:'.config('ai.max_message_length')],
        ]);

        return response()->stream(function () use ($data) {
            try {
                foreach ($this->openRouter->stream($data['messages']) as $chunk) {
                    echo $chunk;
                    ob_flush();
                    flush();
                }
            } catch (OpenRouterException $e) {
                $this->emitSse(['error' => $e->getMessage()]);
                $this->emitSseDone();
            } catch (\Throwable) {
                $this->emitSse(['error' => 'Upstream error']);
                $this->emitSseDone();
            }
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    private function emitSse(array $data): void
    {
        echo 'data: '.json_encode($data)."\n\n";
        ob_flush();
        flush();
    }

    private function emitSseDone(): void
    {
        echo "data: [DONE]\n\n";
        ob_flush();
        flush();
    }
}
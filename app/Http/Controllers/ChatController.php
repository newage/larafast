<?php

namespace App\Http\Controllers;

use App\Faq\ChatPipeline;
use App\Faq\InvalidChatRequest;
use App\Faq\UpstreamFailure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function __invoke(Request $request, ChatPipeline $pipeline): JsonResponse
    {
        try {
            $message = $pipeline->run($request->json('messages'));
        } catch (InvalidChatRequest $e) {
            return $this->error(422, $e->getMessage());
        } catch (UpstreamFailure $e) {
            Log::error('chat upstream failure: '.$e->getMessage(), ['exception' => $e->getPrevious()]);

            return $this->error(502, 'Service temporarily unavailable');
        }

        return response()->json(['type' => 'answer', 'message' => $message]);
    }

    private function error(int $status, string $message): JsonResponse
    {
        return response()->json(['type' => 'error', 'message' => $message], $status);
    }
}

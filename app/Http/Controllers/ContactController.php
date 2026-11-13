<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function __invoke(ContactRequest $request): JsonResponse
    {
        // Honeypot: bots fill the hidden field; answer as if it worked, send nothing.
        if ($request->filled('company_website')) {
            return response()->json(['type' => 'ok']);
        }

        try {
            Mail::to(config('site.contact_email'))->send(new ContactMessage(
                $request->input('name'),
                $request->input('email'),
                $request->input('message'),
            ));
        } catch (Throwable $e) {
            Log::error('contact mail failure: '.$e->getMessage(), ['exception' => $e]);

            return response()->json(['type' => 'error', 'message' => 'Service temporarily unavailable'], 502);
        }

        return response()->json(['type' => 'ok']);
    }
}

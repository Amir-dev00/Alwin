<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        if (filled($request->input('website'))) {
            return response()->json(['ok' => true], 201);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]{8,20}$/'],
            'email' => ['nullable', 'email', 'max:160'],
            'subject' => ['nullable', 'string', 'max:32'],
            'message' => ['nullable', 'string', 'max:2000'],
            'source' => ['nullable', 'string', 'max:32'],
        ]);

        $subject = $data['subject'] ?? 'callback';
        if (! array_key_exists($subject, ContactInquiry::SUBJECTS)) {
            $subject = 'other';
        }

        $source = $data['source'] ?? 'contact';
        if (! array_key_exists($source, ContactInquiry::SOURCES)) {
            $source = 'contact';
        }

        try {
            $inquiry = ContactInquiry::query()->create([
                'name' => trim($data['name']),
                'phone' => preg_replace('/\s+/', '', $data['phone']),
                'email' => $data['email'] ?? null,
                'subject' => $subject,
                'message' => isset($data['message']) ? trim($data['message']) : null,
                'source' => $source,
                'status' => ContactInquiry::STATUS_NEW,
                'ip_address' => $request->ip(),
            ]);
        } catch (Throwable $e) {
            Log::error('contact.failed', ['message' => $e->getMessage()]);
            throw $e;
        }

        Log::info('contact.registered', ['id' => $inquiry->id, 'source' => $source]);

        return response()->json([
            'ok' => true,
            'id' => $inquiry->id,
        ], 201);
    }
}

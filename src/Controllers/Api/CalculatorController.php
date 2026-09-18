<?php

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Models\PricingLead;
use App\Services\PricingCalculator;
use App\Services\PricingCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function catalog(): JsonResponse
    {
        return response()->json(PricingCatalog::payload())
            ->header('Cache-Control', 'public, max-age=15');
    }

    public function estimate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_type_id' => ['nullable', 'integer'],
            'model_id' => ['nullable', 'integer'],
            'width_cm' => ['required', 'numeric', 'min:1', 'max:800'],
            'height_cm' => ['required', 'numeric', 'min:1', 'max:800'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'profile_id' => ['nullable', 'string', 'max:32'],
            'glass_id' => ['nullable', 'string', 'max:32'],
            'hardware_id' => ['nullable', 'string', 'max:32'],
            'hardware_type' => ['nullable', 'string', 'max:32'],
        ]);
        $data['model_id'] = $data['model_id'] ?? $data['product_type_id'] ?? 0;
        $quote = PricingCalculator::quote($data);

        return response()->json($quote);
    }

    public function storeLead(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:32'],
            'product_type_id' => ['nullable', 'integer'],
            'model_id' => ['nullable', 'integer'],
            'width_cm' => ['nullable', 'numeric'],
            'height_cm' => ['nullable', 'numeric'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
            'notes' => ['nullable'],
        ]);

        $modelId = (int) ($data['model_id'] ?? $data['product_type_id'] ?? 0);
        $notes = $data['notes'] ?? null;
        $parsed = [];
        if (is_string($notes)) {
            $decoded = json_decode($notes, true);
            $parsed = is_array($decoded) ? $decoded : ['text' => $notes];
        } elseif (is_array($notes)) {
            $parsed = $notes;
        }

        $catalog = PricingCatalog::payload();
        $model = $catalog['models'][$modelId] ?? null;
        $quote = PricingCalculator::quote([
            'model_id' => $modelId,
            'width_cm' => $data['width_cm'] ?? 0,
            'height_cm' => $data['height_cm'] ?? 0,
            'quantity' => $data['quantity'] ?? 1,
            'profile_id' => $parsed['profile'] ?? null,
            'glass_id' => $parsed['glass'] ?? null,
            'hardware_id' => $parsed['hardware'] ?? null,
            'hardware_type' => $parsed['hardware_type'] ?? null,
        ]);

        $lead = PricingLead::query()->create([
            'model_id' => $modelId ?: null,
            'model_name' => $model['name'] ?? ($parsed['model'] ?? null),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'width_cm' => isset($data['width_cm']) ? (int) $data['width_cm'] : null,
            'height_cm' => isset($data['height_cm']) ? (int) $data['height_cm'] : null,
            'quantity' => (int) ($data['quantity'] ?? 1),
            'profile_key' => $parsed['profile'] ?? null,
            'glass_key' => $parsed['glass'] ?? null,
            'hardware_origin' => $parsed['hardware'] ?? null,
            'hardware_type_key' => $parsed['hardware_type'] ?? null,
            'estimate' => $quote['ok'] ? $quote['total'] : ($parsed['estimate'] ?? null),
            'notes' => is_string($notes) ? $notes : json_encode($parsed, JSON_UNESCAPED_UNICODE),
            'status' => 'new',
        ]);

        return response()->json([
            'ok' => true,
            'id' => $lead->id,
            'estimate' => $quote['ok'] ? $quote['total'] : null,
            'formatted_price' => $quote['formatted_price'] ?? null,
        ], 201);
    }
}

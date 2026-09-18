<?php

namespace App\Services;

final class PricingCalculator
{
    public static function quote(array $input, ?array $catalog = null): array
    {
        $catalog ??= PricingCatalog::payload();
        $modelId = (int) ($input['model_id'] ?? 0);
        $model = $catalog['models'][$modelId] ?? null;
        if (! $model) {
            return self::empty('مدل پیدا نشد.');
        }

        $widthCm = (float) ($input['width_cm'] ?? 0);
        $heightCm = (float) ($input['height_cm'] ?? 0);
        $qty = max(1, (int) ($input['quantity'] ?? 1));
        if ($widthCm <= 0 || $heightCm <= 0) {
            return self::empty('ابعاد نامعتبر است.');
        }

        $widthM = $widthCm / 100;
        $heightM = $heightCm / 100;
        $opening = $widthM * $heightM;
        $settings = $catalog['settings'] ?? [];
        $waste = ((float) ($settings['waste_percent'] ?? 0)) / 100;
        $roundTo = max(1, (int) ($settings['round_to'] ?? 1000));
        $fallback = (string) ($settings['fallback_brand'] ?? 'wintech');

        $brandKey = (string) ($input['profile_id'] ?? $catalog['profiles'][1]['id'] ?? 'wintech');
        $glassKey = (string) ($input['glass_id'] ?? ($catalog['glass'][0]['id'] ?? 'simple'));
        $origin = (string) ($input['hardware_id'] ?? 'turk');
        if (! in_array($origin, ['turk', 'germany'], true)) {
            $origin = 'turk';
        }

        $brand = collect($catalog['profiles'])->firstWhere('id', $brandKey)
            ?? collect($catalog['profiles'])->firstWhere('id', $fallback)
            ?? ($catalog['profiles'][0] ?? null);
        $glass = collect($catalog['glass'])->firstWhere('id', $glassKey)
            ?? ($catalog['glass'][0] ?? null);

        $hardwareTypeKey = self::resolveHardwareType($model, $input['hardware_type'] ?? null);
        $hardwareType = collect($catalog['hardwareTypes'] ?? [])->firstWhere('id', $hardwareTypeKey);

        $recipe = is_array($model['recipe'] ?? null) ? $model['recipe'] : [];
        $usage = PricingBom::usage($recipe, $widthM, $heightM);
        $componentNames = collect($catalog['components'] ?? [])->keyBy('key');

        $profileLines = [];
        $profileTotal = 0;
        foreach ($usage as $key => $meters) {
            $used = $meters * (1 + $waste);
            if ($used < 0.0001) {
                continue;
            }
            $unitPrice = self::componentPrice($brand, $key, $catalog, $fallback);
            $line = (int) round($used * $unitPrice);
            $comp = $componentNames[$key] ?? null;
            $profileLines[] = [
                'key' => $key,
                'name' => $comp['name'] ?? $key,
                'unit' => $comp['unit'] ?? 'meter',
                'qty' => round($used, 3),
                'unit_price' => $unitPrice,
                'total' => $line,
            ];
            $profileTotal += $line;
        }

        $deduction = $model['glassDeduction'];
        $glassArea = 0;
        $glassTotal = 0;
        if ($deduction !== null && ($model['panelRatio'] ?? 0) < 0.999) {
            $glassArea = max(0, $opening - (float) $deduction);
            $glassTotal = (int) round($glassArea * (int) ($glass['pricePerSqm'] ?? 0));
        }

        $hwQty = (int) ($model['hardwareQty'] ?? 0);
        $hwUnit = 0;
        if ($hwQty > 0 && $hardwareType) {
            $hwUnit = (int) ($hardwareType['prices'][$origin] ?? 0);
        }
        $hardwareTotal = $hwQty * $hwUnit;

        $screenTotal = 0;
        $areaRate = (int) ($model['areaRate'] ?? 0);
        if ($areaRate > 0) {
            $screenTotal = (int) round($areaRate * $opening);
        }

        $subtotal = $profileTotal + $glassTotal + $hardwareTotal + $screenTotal;
        $total = (int) (round($subtotal / $roundTo) * $roundTo) * $qty;

        return [
            'ok' => true,
            'error' => null,
            'model_id' => $modelId,
            'model_name' => $model['name'],
            'width_cm' => $widthCm,
            'height_cm' => $heightCm,
            'quantity' => $qty,
            'opening_sqm' => round($opening, 3),
            'glass_area' => round($glassArea, 3),
            'profile_id' => $brand['id'] ?? $brandKey,
            'profile_name' => $brand['name'] ?? $brandKey,
            'glass_id' => $glass['id'] ?? $glassKey,
            'glass_name' => $glass['name'] ?? $glassKey,
            'hardware_origin' => $origin,
            'hardware_type' => $hardwareTypeKey,
            'hardware_type_name' => $hardwareType['name'] ?? '',
            'hardware_qty' => $hwQty,
            'lines' => [
                'profile' => $profileLines,
                'glass' => [
                    'area' => round($glassArea, 3),
                    'unit_price' => (int) ($glass['pricePerSqm'] ?? 0),
                    'total' => $glassTotal,
                ],
                'hardware' => [
                    'qty' => $hwQty,
                    'unit_price' => $hwUnit,
                    'total' => $hardwareTotal,
                ],
                'screen' => [
                    'area' => round($opening, 3),
                    'unit_price' => $areaRate,
                    'total' => $screenTotal,
                ],
            ],
            'profile_total' => $profileTotal,
            'glass_total' => $glassTotal,
            'hardware_total' => $hardwareTotal,
            'screen_total' => $screenTotal,
            'subtotal' => $subtotal,
            'total' => $total,
            'formatted_price' => self::formatToman($total),
        ];
    }

    public static function formatToman(int $n): string
    {
        if ($n <= 0) {
            return '—';
        }

        return number_format($n, 0, '.', ',') . ' تومان';
    }

    private static function resolveHardwareType(array $model, mixed $requested): string
    {
        $mode = $model['hardwareMode'] ?? 'fixed';
        if ($mode === 'none') {
            return 'none';
        }
        if ($mode === 'casement') {
            $req = (string) $requested;

            return in_array($req, ['tilt', 'tilt_turn'], true) ? $req : 'tilt';
        }

        return (string) ($model['hardwareType'] ?? 'tilt');
    }

    private static function componentPrice(?array $brand, string $key, array $catalog, string $fallback): int
    {
        $direct = $brand['prices'][$key] ?? null;
        if ($direct !== null && $direct !== '') {
            return (int) $direct;
        }
        $fallbackBrand = collect($catalog['profiles'])->firstWhere('id', $fallback);
        $fb = $fallbackBrand['prices'][$key] ?? null;
        if ($fb !== null && $fb !== '') {
            return (int) $fb;
        }
        foreach ($catalog['profiles'] as $row) {
            $v = $row['prices'][$key] ?? null;
            if ($v !== null && $v !== '') {
                return (int) $v;
            }
        }

        return 0;
    }

    private static function empty(string $error): array
    {
        return [
            'ok' => false,
            'error' => $error,
            'total' => 0,
            'formatted_price' => '—',
        ];
    }
}

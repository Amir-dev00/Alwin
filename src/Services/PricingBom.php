<?php

namespace App\Services;

/**
 * Builds linear-meter recipes from a model's structure.
 *
 * Each component: meters = (w * width_m) + (h * height_m) + c + (area * width_m * height_m)
 * PANEL uses `area` as a fraction of the opening (sqm).
 */
final class PricingBom
{
    public static function families(): array
    {
        return [
            'casement' => 'لولایی / ثابت',
            'sliding' => 'کشویی',
            'door' => 'درب سوییچی',
            'accordion' => 'آکاردئونی',
            'volkswagen' => 'فولکس واگنی',
            'screen' => 'توری',
        ];
    }

    public static function build(array $model): array
    {
        $lites = max(1, (int) ($model['lites'] ?? 1));
        $sashes = max(0, (int) ($model['sashes'] ?? 0));
        $top = ! empty($model['transom_top']);
        $bot = ! empty($model['transom_bottom']);
        $transoms = ($top ? 1 : 0) + ($bot ? 1 : 0);
        $family = (string) ($model['family'] ?? 'casement');
        $panel = (float) ($model['panel_ratio'] ?? 0);
        $mainH = $transoms > 0 ? 0.72 : 1.0;

        if ($family === 'screen') {
            return [
                'frame' => ['w' => 2, 'h' => 2, 'c' => 0],
            ];
        }

        if ($family === 'sliding' || $family === 'volkswagen') {
            return self::sliding($lites, $sashes, $transoms, $panel, $family);
        }

        $recipe = [
            'frame' => ['w' => 2, 'h' => 2, 'c' => 0],
        ];

        if ($sashes > 0) {
            $key = in_array($family, ['door', 'accordion'], true) ? 'door_sash' : 'sash';
            $recipe[$key] = [
                'w' => round(2 * $sashes / $lites, 4),
                'h' => round(2 * $sashes * $mainH, 4),
                'c' => 0,
            ];
        }

        $vert = max(0, $lites - 1);
        if ($vert > 0) {
            $recipe['mullion'] = ['w' => 0, 'h' => $vert, 'c' => 0];
        }
        if ($transoms > 0) {
            $recipe['mullion_katibe'] = ['w' => $transoms, 'h' => 0, 'c' => 0];
        }

        if ($panel < 0.999) {
            $recipe['zehvar'] = [
                'w' => 2 + $transoms,
                'h' => 2 + $vert,
                'c' => 0,
            ];
        }

        if ($panel > 0) {
            $recipe['panel'] = ['w' => 0, 'h' => 0, 'c' => 0, 'area' => $panel];
        }

        return $recipe;
    }

    private static function sliding(int $lites, int $sashes, int $transoms, float $panel, string $family): array
    {
        $sashes = max(1, $sashes);
        $recipe = [
            'frame_keshoee' => ['w' => 2, 'h' => 2, 'c' => 0],
            'sash_keshoee' => [
                'w' => round(2 * $sashes / $lites, 4),
                'h' => 2 * $sashes,
                'c' => 0,
            ],
            'interlock' => ['w' => 0, 'h' => max(1, $lites - 1), 'c' => 0],
            'overhong' => ['w' => 1, 'h' => 0, 'c' => 0],
            'darpoosh' => ['w' => 1, 'h' => 0, 'c' => 0],
            'zehvar' => [
                'w' => 2 + $transoms,
                'h' => 2 + max(0, $lites - 1),
                'c' => 0,
            ],
        ];

        if ($transoms > 0) {
            $recipe['mullion_katibe'] = ['w' => $transoms, 'h' => 0, 'c' => 0];
        }
        if ($lites > 2) {
            $recipe['conector_sash'] = ['w' => 0, 'h' => $lites - 2, 'c' => 0];
        }
        if ($family === 'volkswagen') {
            $recipe['conector_sash'] = ['w' => 0, 'h' => 1, 'c' => 0];
        }
        if ($panel > 0) {
            $recipe['panel'] = ['w' => 0, 'h' => 0, 'c' => 0, 'area' => $panel];
        }

        return $recipe;
    }

    public static function usage(array $recipe, float $widthM, float $heightM): array
    {
        $out = [];
        foreach ($recipe as $key => $row) {
            if (! is_array($row)) {
                continue;
            }
            $w = (float) ($row['w'] ?? 0);
            $h = (float) ($row['h'] ?? 0);
            $c = (float) ($row['c'] ?? 0);
            $area = (float) ($row['area'] ?? 0);
            $qty = ($w * $widthM) + ($h * $heightM) + $c + ($area * $widthM * $heightM);
            $out[$key] = round(max(0, $qty), 4);
        }

        return $out;
    }
}

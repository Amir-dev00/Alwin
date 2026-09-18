<?php

namespace App\Support;

final class BuildInfo
{
    public static function version(): string
    {
        return (string) (config('alwin.version') ?: '1.0.0');
    }

    public static function label(): string
    {
        return 'ALWIN v'.self::version();
    }

    public static function date(): ?string
    {
        $value = config('alwin.build_date');

        return $value ? (string) $value : null;
    }

    public static function commit(): ?string
    {
        $value = config('alwin.git_commit');

        return $value ? (string) $value : null;
    }

    public static function detectCommit(): ?string
    {
        $git = base_path('.git');
        $head = $git.DIRECTORY_SEPARATOR.'HEAD';
        if (! is_file($head)) {
            return null;
        }
        $ref = trim((string) file_get_contents($head));
        if (str_starts_with($ref, 'ref: ')) {
            $path = $git.DIRECTORY_SEPARATOR.substr($ref, 5);
            if (is_file($path)) {
                return substr(trim((string) file_get_contents($path)), 0, 7);
            }
        }

        return strlen($ref) >= 7 ? substr($ref, 0, 7) : $ref;
    }

    public static function snapshot(): array
    {
        return [
            'version' => self::version(),
            'build_date' => self::date() ?: now()->toDateTimeString(),
            'git_commit' => self::commit() ?: self::detectCommit(),
        ];
    }
}

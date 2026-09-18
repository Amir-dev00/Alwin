<?php

namespace App\Support;

use ZipArchive;

final class UnixZipPerms
{
    public static function apply(ZipArchive $zip, string $name, bool $directory = false, bool $executable = false): void
    {
        $name = str_replace('\\', '/', $name);
        $mode = $directory ? 040755 : ($executable ? 0100755 : 0100644);
        // Unix mode only — no DOS bits. Hosts like Mihan reject 0666/0677.
        $attr = $mode << 16;
        $entries = $directory
            ? [rtrim($name, '/'), rtrim($name, '/').'/']
            : [$name];

        foreach ($entries as $entry) {
            $zip->setExternalAttributesName($entry, ZipArchive::OPSYS_UNIX, $attr);
        }
    }

    public static function isExecutable(string $local): bool
    {
        $local = str_replace('\\', '/', $local);
        $base = basename($local);

        return $base === 'artisan' || str_starts_with($local, 'vendor/bin/');
    }

    public static function normalizeArchive(ZipArchive $zip): void
    {
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === false) {
                continue;
            }
            $opsys = 0;
            $attr = 0;
            $zip->getExternalAttributesName($name, $opsys, $attr);
            $type = ($attr >> 16) & 0170000;
            $directory = str_ends_with($name, '/') || $type === 0040000;
            self::apply($zip, $name, $directory, ! $directory && self::isExecutable($name));
        }
    }
}

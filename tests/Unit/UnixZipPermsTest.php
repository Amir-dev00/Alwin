<?php

namespace Tests\Unit;

use App\Support\UnixZipPerms;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use ZipArchive;

class UnixZipPermsTest extends TestCase
{
    #[Test]
    public function zip_entries_get_unix_644_and_755_not_666(): void
    {
        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'alwin-unix-zip-'.uniqid('', true).'.zip';
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true);
        $zip->addFromString('index.php', "<?php\n");
        UnixZipPerms::apply($zip, 'index.php');
        $zip->addEmptyDir('storage');
        UnixZipPerms::apply($zip, 'storage', true);
        $zip->addFromString('artisan', "#!/usr/bin/env php\n");
        UnixZipPerms::apply($zip, 'artisan', false, true);
        $zip->close();

        $zip = new ZipArchive;
        $zip->open($path);

        $this->assertSame(0644, $this->unixMode($zip, 'index.php') & 0777);
        $this->assertSame(0755, $this->unixMode($zip, 'storage/') & 0777);
        $this->assertSame(0755, $this->unixMode($zip, 'artisan') & 0777);
        $this->assertSame(0, $this->unixMode($zip, 'index.php') & 02, 'files must not be world-writable');

        $zip->close();
        @unlink($path);
    }

    private function unixMode(ZipArchive $zip, string $name): int
    {
        $opsys = 0;
        $attr = 0;
        $this->assertTrue($zip->getExternalAttributesName($name, $opsys, $attr));
        $this->assertSame(ZipArchive::OPSYS_UNIX, $opsys);

        return ($attr >> 16) & 0xFFFF;
    }
}

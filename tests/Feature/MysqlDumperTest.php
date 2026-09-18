<?php

namespace Tests\Feature;

use App\Support\MysqlDumper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MysqlDumperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_dump_is_valid_mysql_without_sqlite_artifacts(): void
    {
        $sql = MysqlDumper::dump();

        $this->assertStringNotContainsString('`main.', $sql);
        $this->assertStringNotContainsString("DEFAULT '\\'", $sql);
        $this->assertStringContainsString('CREATE TABLE `media`', $sql);
        $this->assertStringContainsString('CREATE TABLE `users`', $sql);
        $this->assertStringContainsString('Do NOT import this into a live database that already has data', $sql);
        $this->assertMatchesRegularExpression('/`size` BIGINT UNSIGNED NOT NULL DEFAULT 0/', $sql);
        $this->assertMatchesRegularExpression("/`disk` VARCHAR\\(255\\) NOT NULL DEFAULT 'public'/", $sql);
        $this->assertStringContainsString('INSERT INTO `users`', $sql);
        $this->assertStringContainsString('INSERT INTO `articles`', $sql);
        $this->assertStringContainsString('ENGINE=InnoDB', $sql);
    }

    public function test_additive_dump_is_mysql_and_keeps_existing_rows(): void
    {
        $sql = MysqlDumper::additiveDump();

        $this->assertStringNotContainsString('DROP TABLE', $sql);
        $this->assertStringNotContainsString('TRUNCATE', $sql);
        $this->assertStringContainsString('CREATE TABLE IF NOT EXISTS `users`', $sql);
        $this->assertStringContainsString('INSERT IGNORE INTO `users`', $sql);
        $this->assertStringContainsString('INSERT IGNORE INTO `articles`', $sql);
        $this->assertStringContainsString('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4', $sql);
        $this->assertStringContainsString('information_schema.COLUMNS', $sql);
        $this->assertStringNotContainsString('INSERT IGNORE INTO `migrations`', $sql);
        $this->assertStringNotContainsString('`main.', $sql);
    }
}

<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MysqlDumper
{
    /** Operational tables: schema is exported, rows are not. */
    private const SKIP_DATA = [
        'cache',
        'cache_locks',
        'sessions',
        'jobs',
        'job_batches',
        'failed_jobs',
        'password_reset_tokens',
        'contact_inquiries',
        'pricing_leads',
        'activity_logs',
    ];

    public static function dump(): string
    {
        $tables = self::tables();

        $sql = "-- ALWIN Laravel dump (MySQL 5.7+ / 8)\n";
        $sql .= "-- SAFETY: first install or backup restore only.\n";
        $sql .= "-- Do NOT import this into a live database that already has data.\n";
        $sql .= "-- This file DROPS tables, then recreates them.\n";
        $sql .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table => $schemaTable) {
            $sql .= 'DROP TABLE IF EXISTS `'.$table."`;\n";
            $sql .= self::createTable($table, $schemaTable)."\n";
            if (! in_array($table, self::SKIP_DATA, true)) {
                $sql .= self::insertRows($table)."\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * MySQL 5.7+ / 8 dump for phpMyAdmin on an existing database.
     * No DROP/TRUNCATE/DELETE. Missing tables/columns are added. Live rows win on key clashes.
     */
    public static function additiveDump(): string
    {
        $tables = self::tables();
        $skipData = array_merge(self::SKIP_DATA, ['migrations']);

        $sql = "-- ALWIN additive dump for phpMyAdmin (MySQL 5.7+ / 8, InnoDB, utf8mb4)\n";
        $sql .= "-- Import into the EXISTING MySQL database. Do not drop the database first.\n";
        $sql .= "-- Does NOT drop tables. Does NOT delete or replace existing rows.\n";
        $sql .= "-- Missing tables/columns are added. INSERT IGNORE keeps live data when keys already exist.\n";
        $sql .= "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n";

        foreach ($tables as $table => $schemaTable) {
            $sql .= self::createTable($table, $schemaTable, true)."\n";
            $sql .= self::ensureColumnsSql($table, $schemaTable);
            if (! in_array($table, $skipData, true)) {
                $sql .= self::insertRows($table, true)."\n";
                $sql .= self::bumpAutoIncrementSql($table, $schemaTable);
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        return $sql;
    }

    /**
     * @return array<string, string>
     */
    private static function tables(): array
    {
        $tables = [];
        foreach (Schema::getTableListing() as $raw) {
            $table = self::bare($raw);
            if ($table === '' || str_starts_with($table, 'sqlite_')) {
                continue;
            }
            $tables[$table] = $raw;
        }

        return $tables;
    }

    private static function createTable(string $table, string $schemaTable, bool $ifNotExists = false): string
    {
        $columns = self::columns($schemaTable, $table);
        $cols = [];
        $primary = [];

        foreach ($columns as $col) {
            $line = self::columnDefinition($col);
            if (! empty($col['auto_increment'])) {
                $primary[] = $col['name'];
            }
            $cols[] = $line;
        }

        $hasPrimary = false;
        $indexSql = [];
        foreach (Schema::getIndexes($schemaTable) as $index) {
            $indexName = self::bare((string) ($index['name'] ?? ''));
            if ($indexName === '' || str_starts_with($indexName, 'sqlite_')) {
                continue;
            }
            $indexCols = array_map(fn ($c) => '`'.self::bare((string) $c).'`', $index['columns'] ?? []);
            if ($indexCols === []) {
                continue;
            }
            if (! empty($index['primary'])) {
                if ($hasPrimary) {
                    continue;
                }
                $indexSql[] = 'PRIMARY KEY ('.implode(', ', $indexCols).')';
                $hasPrimary = true;
                continue;
            }
            $prefix = ! empty($index['unique']) ? 'UNIQUE KEY' : 'KEY';
            $indexSql[] = $prefix.' `'.$indexName.'` ('.implode(', ', $indexCols).')';
        }
        if (! $hasPrimary && $primary !== []) {
            $indexSql[] = 'PRIMARY KEY ('.implode(', ', array_map(fn ($c) => '`'.$c.'`', $primary)).')';
        }

        $keyword = $ifNotExists ? 'CREATE TABLE IF NOT EXISTS `' : 'CREATE TABLE `';

        return $keyword.$table."` (\n  ".implode(",\n  ", array_merge($cols, $indexSql))."\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n";
    }

    private static function columnDefinition(array $col, bool $allowAutoIncrement = true): string
    {
        $name = $col['name'];
        $mysqlType = self::mysqlType($col);
        $nullable = (bool) ($col['nullable'] ?? true);
        $auto = $allowAutoIncrement && ! empty($col['auto_increment']);
        $line = '`'.$name.'` '.$mysqlType;

        if (! $nullable && ! str_contains($mysqlType, ' NULL')) {
            $line .= ' NOT NULL';
        }
        if ($auto) {
            $line .= ' AUTO_INCREMENT';
        } elseif (array_key_exists('default', $col) && $col['default'] !== null) {
            $default = self::defaultSql($col['default'], $col);
            if ($default !== null) {
                $line .= ' DEFAULT '.$default;
            }
        }

        return $line;
    }

    private static function ensureColumnsSql(string $table, string $schemaTable): string
    {
        $sql = '';
        foreach (self::columns($schemaTable, $table) as $col) {
            if (! empty($col['auto_increment'])) {
                continue;
            }
            $name = $col['name'];
            $alter = 'ALTER TABLE `'.$table.'` ADD COLUMN '.self::columnDefinition($col, false);
            $sql .= 'SET @alwin_exists := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '.self::quote($table).' AND COLUMN_NAME = '.self::quote($name).");\n";
            $sql .= 'SET @alwin_sql := IF(@alwin_exists > 0, \'SELECT 1\', '.self::quote($alter).");\n";
            $sql .= "PREPARE alwin_stmt FROM @alwin_sql;\nEXECUTE alwin_stmt;\nDEALLOCATE PREPARE alwin_stmt;\n\n";
        }

        return $sql;
    }

    private static function bumpAutoIncrementSql(string $table, string $schemaTable): string
    {
        $hasAutoId = false;
        foreach (self::columns($schemaTable, $table) as $col) {
            if ($col['name'] === 'id' && ! empty($col['auto_increment'])) {
                $hasAutoId = true;
                break;
            }
        }
        if (! $hasAutoId) {
            return '';
        }

        return 'SET @alwin_ai := (SELECT IFNULL(MAX(`id`), 0) + 1 FROM `'.$table."`);\n"
            .'SET @alwin_sql := CONCAT(\'ALTER TABLE `'.$table.'` AUTO_INCREMENT=\', @alwin_ai);'."\n"
            ."PREPARE alwin_stmt FROM @alwin_sql;\nEXECUTE alwin_stmt;\nDEALLOCATE PREPARE alwin_stmt;\n\n";
    }

    private static function columns(string $schemaTable, string $table): array
    {
        try {
            return Schema::getColumns($schemaTable);
        } catch (\Throwable) {
            return Schema::getColumns($table);
        }
    }

    private static function mysqlType(array $col): string
    {
        $name = $col['name'];
        $type = strtolower((string) ($col['type_name'] ?? $col['type'] ?? 'text'));
        $full = strtolower((string) ($col['type'] ?? ''));

        if (! empty($col['auto_increment'])) {
            return 'BIGINT UNSIGNED';
        }
        if (self::isBooleanColumn($name)) {
            return 'TINYINT(1)';
        }
        if (self::isJsonColumn($name, $full, $type)) {
            return 'JSON';
        }
        if (in_array($name, ['created_at', 'updated_at', 'deleted_at', 'published_at', 'email_verified_at', 'last_login_at'], true)
            || str_contains($type, 'date') || str_contains($type, 'time')) {
            return 'DATETIME';
        }
        if (in_array($name, ['body', 'description', 'payload', 'exception', 'notes', 'user_agent', 'value'], true)
            || str_contains($type, 'text') || str_contains($full, 'text')) {
            return in_array($name, ['payload', 'body', 'description', 'value', 'exception'], true) ? 'LONGTEXT' : 'TEXT';
        }
        if (str_contains($type, 'blob')) {
            return 'LONGBLOB';
        }
        if (preg_match('/decimal\((\d+),\s*(\d+)\)/', $full, $m)) {
            return 'DECIMAL('.$m[1].','.$m[2].')';
        }
        if (in_array($type, ['float', 'double', 'real', 'numeric', 'decimal'], true)) {
            return 'DOUBLE';
        }
        if (str_contains($type, 'int') || str_contains($full, 'int')) {
            return 'BIGINT UNSIGNED';
        }
        $len = 255;
        if (preg_match('/varchar\((\d+)\)/i', $full, $m)) {
            $len = (int) $m[1];
        }

        return 'VARCHAR('.$len.')';
    }

    private static function isBooleanColumn(string $name): bool
    {
        return str_starts_with($name, 'is_')
            || str_starts_with($name, 'has_')
            || in_array($name, ['show_on_home', 'show_on_listing', 'transom_top', 'transom_bottom', 'recipe_locked'], true);
    }

    private static function isJsonColumn(string $name, string $full, string $type): bool
    {
        return in_array($name, ['specifications', 'properties', 'recipe'], true)
            || str_contains($type, 'json')
            || str_contains($full, 'json');
    }

    private static function defaultSql(mixed $default, array $col): ?string
    {
        $raw = self::unwrapDefault($default);
        if ($raw === null || $raw === 'NULL') {
            return ($col['nullable'] ?? true) ? 'NULL' : null;
        }
        if (is_string($raw) && preg_match('/^current_timestamp/i', $raw)) {
            return 'CURRENT_TIMESTAMP';
        }

        $name = $col['name'];
        $type = strtolower((string) ($col['type_name'] ?? $col['type'] ?? ''));
        $numeric = self::isBooleanColumn($name)
            || str_contains($type, 'int')
            || str_contains($type, 'decimal')
            || str_contains($type, 'float')
            || str_contains($type, 'double')
            || str_contains($type, 'real');

        if ($numeric) {
            if ($raw === '' || $raw === false) {
                return '0';
            }
            if ($raw === true) {
                return '1';
            }
            if (is_numeric($raw)) {
                return (string) (str_contains((string) $raw, '.') ? (float) $raw : (int) $raw);
            }
        }

        return self::quote($raw);
    }

    private static function unwrapDefault(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }
        $value = trim($value);
        if ($value === '') {
            return $value;
        }
        if ((str_starts_with($value, "'") && str_ends_with($value, "'") && strlen($value) >= 2)
            || (str_starts_with($value, '"') && str_ends_with($value, '"') && strlen($value) >= 2)) {
            return str_replace("''", "'", substr($value, 1, -1));
        }

        return $value;
    }

    private static function insertRows(string $table, bool $ignore = false): string
    {
        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            return '';
        }
        $columnMeta = Schema::getColumns($table);
        $columnNames = array_map(fn ($col) => '`'.$col['name'].'`', $columnMeta);
        $sql = '';
        $verb = $ignore ? 'INSERT IGNORE INTO `' : 'INSERT INTO `';
        foreach ($rows->chunk(40) as $chunk) {
            $values = [];
            foreach ($chunk as $row) {
                $arr = (array) $row;
                $cells = [];
                foreach ($columnMeta as $col) {
                    $cells[] = self::quoteValue($arr[$col['name']] ?? null, $col);
                }
                $values[] = '('.implode(', ', $cells).')';
            }
            $sql .= $verb.$table.'` ('.implode(', ', $columnNames).") VALUES\n".implode(",\n", $values).";\n";
        }

        return $sql;
    }

    private static function quoteValue(mixed $value, array $col): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (self::isBooleanColumn($col['name']) || is_bool($value)) {
            return $value ? '1' : '0';
        }
        $type = strtolower((string) ($col['type_name'] ?? $col['type'] ?? ''));
        if ((str_contains($type, 'int') || str_contains($type, 'decimal') || str_contains($type, 'float') || str_contains($type, 'double'))
            && is_numeric($value) && ! self::isJsonColumn($col['name'], $type, $type)) {
            return (string) $value;
        }

        return self::quote($value);
    }

    private static function quote(mixed $value): string
    {
        $value = str_replace(
            ["\\", "\0", "\n", "\r", "'", "\x1a"],
            ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\Z'],
            (string) $value
        );

        return "'".$value."'";
    }

    private static function bare(string $name): string
    {
        $name = trim(str_replace('`', '', $name));
        if (str_contains($name, '.')) {
            $name = substr($name, strrpos($name, '.') + 1);
        }

        return $name;
    }
}

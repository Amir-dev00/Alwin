<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Production seed is additive only. Existing rows are not overwritten.');
            $this->command?->warn('Prefer CmsContentSeeder to add missing CMS keys. Never use migrate:fresh, db:wipe, or re-import SQL.');
        }

        $this->call(CmsSeeder::class);
        $this->call(ArticleSeeder::class);
    }
}

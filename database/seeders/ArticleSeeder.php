<?php

namespace Database\Seeders;

use App\Support\ArticleImporter;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    public function run(): void
    {
        $count = ArticleImporter::import();
        $this->command?->info("Imported {$count} articles.");
    }
}

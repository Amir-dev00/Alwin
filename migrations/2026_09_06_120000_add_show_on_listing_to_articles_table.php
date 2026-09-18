<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('articles', 'show_on_listing')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->boolean('show_on_listing')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('articles', 'show_on_listing')) {
            return;
        }

        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn('show_on_listing');
        });
    }
};

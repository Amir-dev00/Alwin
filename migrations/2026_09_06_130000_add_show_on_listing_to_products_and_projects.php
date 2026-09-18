<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'show_on_listing')) {
            Schema::table('products', function (Blueprint $table) {
                $table->boolean('show_on_listing')->default(true)->after('show_on_home');
            });
        }
        if (! Schema::hasColumn('projects', 'show_on_listing')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->boolean('show_on_listing')->default(true)->after('show_on_home');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'show_on_listing')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('show_on_listing');
            });
        }
        if (Schema::hasColumn('projects', 'show_on_listing')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->dropColumn('show_on_listing');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pricing_brands', 'material')) {
            Schema::table('pricing_brands', function (Blueprint $table) {
                $table->string('material', 16)->default('upvc')->after('name');
                $table->index('material');
            });
        }

        if (! Schema::hasColumn('pricing_models', 'area_rate')) {
            Schema::table('pricing_models', function (Blueprint $table) {
                $table->unsignedBigInteger('area_rate')->nullable()->after('panel_ratio');
            });
        }
        if (! Schema::hasColumn('pricing_models', 'image')) {
            Schema::table('pricing_models', function (Blueprint $table) {
                $table->string('image')->nullable()->after('area_rate');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pricing_brands', 'material')) {
            Schema::table('pricing_brands', function (Blueprint $table) {
                $table->dropIndex(['material']);
                $table->dropColumn('material');
            });
        }
        if (Schema::hasColumn('pricing_models', 'image')) {
            Schema::table('pricing_models', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
        if (Schema::hasColumn('pricing_models', 'area_rate')) {
            Schema::table('pricing_models', function (Blueprint $table) {
                $table->dropColumn('area_rate');
            });
        }
    }
};

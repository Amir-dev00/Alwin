<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_brands', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pricing_components', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->string('unit', 16)->default('meter');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('pricing_brand_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('pricing_brands')->cascadeOnDelete();
            $table->string('component_key', 64);
            $table->unsignedBigInteger('price')->nullable();
            $table->timestamps();
            $table->unique(['brand_id', 'component_key']);
        });

        Schema::create('pricing_glasses', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name');
            $table->unsignedBigInteger('price_per_sqm')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pricing_hardwares', function (Blueprint $table) {
            $table->id();
            $table->string('key', 32)->unique();
            $table->string('name');
            $table->unsignedBigInteger('price_turk')->default(0);
            $table->unsignedBigInteger('price_germany')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('pricing_models', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('catalog_id')->unique();
            $table->string('name');
            $table->string('tab', 16)->default('windows');
            $table->string('category_key', 32)->default('casement');
            $table->string('family', 32)->default('casement');
            $table->decimal('glass_deduction', 8, 2)->nullable();
            $table->unsignedTinyInteger('hardware_qty')->default(1);
            $table->string('hardware_mode', 16)->default('fixed');
            $table->string('hardware_type_key', 32)->nullable();
            $table->unsignedTinyInteger('lites')->default(1);
            $table->unsignedTinyInteger('sashes')->default(0);
            $table->boolean('transom_top')->default(false);
            $table->boolean('transom_bottom')->default(false);
            $table->decimal('panel_ratio', 8, 2)->default(0);
            $table->json('recipe')->nullable();
            $table->boolean('recipe_locked')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['tab', 'category_key']);
        });

        Schema::create('pricing_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label')->nullable();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        Schema::create('pricing_leads', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('model_id')->nullable();
            $table->string('model_name')->nullable();
            $table->string('name');
            $table->string('phone', 32);
            $table->unsignedInteger('width_cm')->nullable();
            $table->unsignedInteger('height_cm')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->string('profile_key', 32)->nullable();
            $table->string('glass_key', 32)->nullable();
            $table->string('hardware_origin', 32)->nullable();
            $table->string('hardware_type_key', 32)->nullable();
            $table->unsignedBigInteger('estimate')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 16)->default('new');
            $table->timestamps();
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_leads');
        Schema::dropIfExists('pricing_settings');
        Schema::dropIfExists('pricing_models');
        Schema::dropIfExists('pricing_hardwares');
        Schema::dropIfExists('pricing_glasses');
        Schema::dropIfExists('pricing_brand_prices');
        Schema::dropIfExists('pricing_components');
        Schema::dropIfExists('pricing_brands');
    }
};

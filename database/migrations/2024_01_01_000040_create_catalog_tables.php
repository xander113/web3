<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['hat', 'head', 'face', 'shirt', 'pants', 'tshirt', 'gear', 'decal']);
            $table->unsignedInteger('price')->default(0);
            $table->string('asset_id')->nullable();
            $table->string('data_file')->nullable();
            $table->boolean('approved')->default(false);
            $table->boolean('declined')->default(false);
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });

        Schema::create('owned_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_item_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'catalog_item_id']);
        });

        Schema::create('equipped_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('catalog_item_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('asset_string')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'catalog_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipped_items');
        Schema::dropIfExists('owned_items');
        Schema::dropIfExists('catalog_items');
    }
};

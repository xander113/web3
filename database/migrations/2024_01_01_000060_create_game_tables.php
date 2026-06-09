<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('ip')->nullable();
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('server_key')->nullable();
            $table->string('private_key')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        Schema::create('game_joins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('game_server_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'game_server_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_joins');
        Schema::dropIfExists('game_servers');
    }
};

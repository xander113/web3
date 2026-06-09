<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('friend_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('friend_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('badge_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badges');
        Schema::dropIfExists('friend_requests');
        Schema::dropIfExists('friends');
    }
};

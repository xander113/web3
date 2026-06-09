<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username', 20)->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->unsignedTinyInteger('rank')->default(0);
            $table->boolean('banned')->default(false);
            $table->string('ban_reason')->nullable();
            $table->timestamp('ban_expires_at')->nullable();
            $table->bigInteger('coins')->default(10);
            $table->text('about')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->string('email_verification_token')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->boolean('hide_status')->default(false);
            $table->integer('post_count')->default(0);
            $table->string('register_ip', 45)->nullable();
            $table->string('last_ip', 45)->nullable();
            $table->string('game_key')->nullable();
            $table->boolean('in_game')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_post_at')->nullable();
            $table->timestamp('last_friend_request_at')->nullable();
            $table->string('avatar_hash')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_developer')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('forums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('forum_categories')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('post_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->boolean('is_developer')->default(false);
            $table->timestamps();
        });

        Schema::create('forum_topics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->boolean('is_locked')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->integer('reply_count')->default(0);
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamps();
        });

        Schema::create('forum_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->foreignId('forum_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('body');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('forum_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained('forum_topics')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'topic_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_reads');
        Schema::dropIfExists('forum_replies');
        Schema::dropIfExists('forum_topics');
        Schema::dropIfExists('forums');
        Schema::dropIfExists('forum_categories');
    }
};

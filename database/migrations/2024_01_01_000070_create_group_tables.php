<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedInteger('member_count')->default(1);
            $table->timestamps();
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_owner')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('groups');
    }
};

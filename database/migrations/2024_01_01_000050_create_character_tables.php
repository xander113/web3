<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('character_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['head', 'torso', 'left_arm', 'right_arm', 'left_leg', 'right_leg']);
            $table->string('color')->default('#FFCC99');
            $table->timestamps();

            $table->unique(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('character_colors');
    }
};

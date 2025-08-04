<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('emotion_colors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->conatrained()->onDelete('cascade');
            $table->string('emotion_state'); // Enumで制御
            $table->string('color_code', 7); // 例: #FFDDDD
            $table->timestamps();

            $table->unique(['user_id', 'emotion_state']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emotion_colors');
    }
};

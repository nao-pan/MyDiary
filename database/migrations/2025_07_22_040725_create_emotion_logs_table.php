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
        Schema::create('emotion_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('diary_id')->constrained()->onDelete('cascade');
            $table->string('emotion_state');
            $table->float('score', 3, 2); // 例: 0.87
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emotion_logs');
    }
};

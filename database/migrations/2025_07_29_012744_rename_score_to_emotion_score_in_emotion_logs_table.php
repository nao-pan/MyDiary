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
        Schema::table('emotion_logs', function (Blueprint $table) {
            $table->renameColumn('score', 'emotion_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emotion_logs', function (Blueprint $table) {
            $table->renameColumn('emotion_score', 'score');
        });
    }
};

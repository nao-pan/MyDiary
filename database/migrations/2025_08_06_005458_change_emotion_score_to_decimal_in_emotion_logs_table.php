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
            // DECIMAL(3,1): 例 0.0 ～ 9.9 までの1桁小数を保持可能
            $table->decimal('emotion_score', 3, 1)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emotion_logs', function (Blueprint $table) {
            $table->float('emotion_score')->change();
        });
    }
};

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
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->integer('new_users')->default(0);
            $table->integer('posts')->default(0);
            $table->decimal('d1_retention', 5, 2)->default(0.00);
            $table->decimal('d7_retention', 5, 2)->default(0.00);
            $table->integer('wau')->default(0);
            $table->integer('mau')->default(0);
            $table->decimal('weekly_3plus_ratio', 5, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};

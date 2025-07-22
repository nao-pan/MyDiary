<?php

namespace Database\Seeders;

use App\Models\Diary;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\EmotionLog;

class EmotionLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Diary::all()->each(function ($diary) {
            // 各日記に対してEmotionLogを生成
            EmotionLog::factory()
                ->for($diary)
                ->count(3) // 各日記に3つのEmotionLogを紐づける
                ->create();
        });
    }
}

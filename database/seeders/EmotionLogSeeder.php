<?php

namespace Database\Seeders;

use App\Models\Diary;
use App\Models\EmotionLog;
use Illuminate\Database\Seeder;

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
                ->count(1)
                ->create();
        });
    }
}

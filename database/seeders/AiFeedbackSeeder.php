<?php

namespace Database\Seeders;

use App\Models\AiFeedback;
use Illuminate\Database\Seeder;
use App\Models\Diary;

class AiFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Diary::all()->each(function ($diary) {
            AiFeedback::factory()
                ->for($diary)
                ->create();
        });
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Diary;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //10人のユーザーを作成、それぞれに5件の日記を紐づける
        User::factory()
            ->count(10)
            ->has(Diary::factory()->count(5))
            ->create();
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Tag;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        collect(['仕事', '趣味', '健康', '人間関係', '学習'])->each(function ($tagName) {
            Tag::factory()->create(['name' => $tagName]);
        });
    }
}

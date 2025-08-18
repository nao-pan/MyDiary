<?php

namespace Database\Seeders;

use App\Models\Diary;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class DiaryTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tagIds = Tag::pluck('id')->toArray();

        Diary::all()->each(function ($diary) use ($tagIds) {
            $diary->tags()->attach(
                collect($tagIds)->random(2)->all()
            );
        });
    }
}

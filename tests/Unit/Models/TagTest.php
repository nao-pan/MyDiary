<?php

namespace Tests\Unit\Models;

use App\Models\Diary;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_create_tag()
    {
        $tag = Tag::create(['name' => 'Test Tag']);

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'name' => 'Test Tag',
        ]);
    }

    public function test_tag_has_expected_fillable_attributes()
    {
        $tag = new Tag;

        $this->assertEquals([
            'name',
        ], $tag->getFillable());
    }

    public function test_tag_belongs_to_many_diaries()
    {
        $tag = Tag::factory()->create();
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);
        $tag->diaries()->attach($diary);

        $relation = $tag->diaries();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $tag->load('diaries');
        $this->assertTrue($tag->diaries->contains($diary));
    }
}

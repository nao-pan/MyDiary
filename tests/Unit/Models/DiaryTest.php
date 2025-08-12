<?php

namespace Tests\Unit\Models;

use App\Models\Diary;
use Tests\TestCase;
use App\Models\User;
use App\Models\EmotionLog;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DiaryTest extends TestCase
{
  public function test_diary_creation()
  {
      $diary = new Diary();
      $this->assertInstanceOf(Diary::class, $diary);
  }

    public function test_diary_has_expected_fillable_attributes()
    {
        $diary = new Diary();

        $this->assertEquals([
            'title',
            'content',
            'user_id',
            'happiness_score'
        ], $diary->getFillable());
    }

    public function test_diary_belongs_to_user()
    {
        $diary = new Diary();
        $relation = $diary->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
        $this->assertEquals('id', $relation->getOwnerKeyName());
    }

    public function test_diary_has_one_emotion_log()
    {
        $diary = new Diary();
        $relation = $diary->emotionLog();

        $this->assertInstanceOf(HasOne::class, $relation);
        $this->assertEquals('diary_id', $relation->getForeignKeyName());
    }

    public function test_diary_belongs_to_many_tags()
    {
        $diary = new Diary();
        $relation = $diary->tags();

        $this->assertInstanceOf(BelongsToMany::class, $relation);
        $this->assertEquals('diary_tag', $relation->getTable());
    }
}
<?php

namespace Tests\Unit\Models;

use App\Models\UnlockedEmotion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

class UnlockedEmotionTest extends TestCase
{
    public function test_unlocked_emotion_creation()
    {
        $unlockedEmotion = new UnlockedEmotion;
        $this->assertInstanceOf(UnlockedEmotion::class, $unlockedEmotion);
    }

    public function test_unlocked_emotion_has_expected_fillable_attributes()
    {
        $unlockedEmotion = new UnlockedEmotion;

        $this->assertEquals([
            'user_id',
            'emotion_state',
            'diary_id',
            'unlocked_at',
        ], $unlockedEmotion->getFillable());
    }

    public function test_unlocked_emotion_belongs_to_diary()
    {
        $unlockedEmotion = new UnlockedEmotion;
        $relation = $unlockedEmotion->diary();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('diary_id', $relation->getForeignKeyName());
    }

    public function test_unlocked_emotion_belongs_to_user()
    {
        $unlockedEmotion = new UnlockedEmotion;
        $relation = $unlockedEmotion->user();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('user_id', $relation->getForeignKeyName());
    }
}

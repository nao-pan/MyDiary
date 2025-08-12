<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\EmotionLog;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\EmotionState;

class EmotionLogTest extends TestCase
{
    public function test_emotion_log_creation()
    {
        $emotionLog = new EmotionLog();
        $this->assertInstanceOf(EmotionLog::class, $emotionLog);
    }
    public function test_emotion_log_has_expected_fillable_attributes()
    {
        $emotionLog = new EmotionLog();

        $this->assertEquals([
            'diary_id',
            'emotion_state',
            'emotion_score'
        ], $emotionLog->getFillable());
    }

    public function test_emotion_log_belongs_to_diary()
    {
        $emotionLog = new EmotionLog();
        $relation = $emotionLog->diary();

        $this->assertInstanceOf(BelongsTo::class, $relation);
        $this->assertEquals('diary_id', $relation->getForeignKeyName());
    }

    public function test_emotion_log_casts()
    {
        $emotionLog = new EmotionLog();

        $this->assertEquals([
            'emotion_state' => EmotionState::class,
            'emotion_score' => 'decimal:1',
            'created_at' => 'datetime',
            'id' => 'int'
        ], $emotionLog->getCasts());
    }
}

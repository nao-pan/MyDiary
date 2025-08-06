<?php

namespace Tests\Feature;

use App\Services\EmotionLogService;
use App\Models\EmotionLog;
use App\Models\Diary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class EmotionLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmotionLogService $service;

    public function setUp(): void
    {
        parent::setUp();
        // EmotionLogService をモック
        $this->service = new EmotionLogService();
    }

    /**
     * 正常に感情ログが保存されることを確認
     */
    public function test_emotion_log_can_be_created()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $diary = Diary::factory()->create([
            'user_id' => $user->id,
        ]);

        $data = [

            'diary_id' => $diary->id,
            'emotion_state' => 'happy',
            'emotion_score' => 0.8,
        ];

        $log = $this->service->create($data);

        $this->assertDatabaseHas('emotion_logs', [
            'id' => $log->id,
            'diary_id' => $diary->id,
            'emotion_state' => 'happy',
            'emotion_score' => 0.8,
        ]);

        $this->assertInstanceOf(EmotionLog::class, $log);
    }

    /**
     * 不正なデータで例外が発生することを確認
     */
    public function test_emotion_log_create_fails_with_invalid_data()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        $this->service->create([
            'diary_id' => null,
            'emotion_state' => 'angry',
            'emotion_score' => 0.4,
        ]);
    }
}

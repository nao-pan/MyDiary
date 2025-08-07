<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Mockery;
use App\Services\EmotionStatusService;
use App\Services\UnlockEvaluator;
use App\Rules\UnlockRuleRepository;
use App\Models\User;
use App\Enums\EmotionState;
use App\Models\Diary;
use App\Models\EmotionLog;


use Tests\TestCase;

class EmotionStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $ruleRepository;
    protected $evaluator;
    protected EmotionStatusService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ruleRepository = Mockery::mock(UnlockRuleRepository::class);
        $this->evaluator = Mockery::mock(UnlockEvaluator::class);

        $this->service = new EmotionStatusService(
            $this->ruleRepository,
            $this->evaluator

        );
    }

    /**
     * ユーザーの感情ステータスが正しく構築されることを検証するテスト
     */
    public function test_builds_emotion_statuses_correctly()
    {
        $user = User::factory()->create();

        $happy = EmotionState::GRATEFUL;

        // ユーザーに HAPPY 感情が解禁されている状態にする
        $user->unlockedEmotions()->create([
            'user_id' => $user->id,
            'emotion_state' => $happy->value,
            'unlocked_at' => now(),
        ]);

        // UnlockRule は null（初期解禁と仮定）
        $this->ruleRepository
            ->shouldReceive('getByEmotion')
            ->andReturn(null);

        $this->evaluator
            ->shouldReceive('isUnlocked')
            ->andReturn(false);

        $statuses = $this->service->buildEmotionStatuses($user, 5);

        $this->assertInstanceOf(Collection::class, $statuses);
        $this->assertTrue(
            $statuses->firstWhere('key', $happy->value)->unlocked
        );
    }

    public function it_returns_recent_emotion_scores_correctly()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);

        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::HAPPY->value,
            'score' => 0.8,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        $scores = $this->service->getRecentEmotionScores($user, 30);

        $this->assertIsArray($scores);
        $this->assertNotEmpty($scores);
        $this->assertGreaterThanOrEqual(1, array_sum($scores));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

<?php

namespace Tests\Unit\Services;

use App\Dto\EmotionStatus;
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
use App\Rules\EmotionUnlockRule;
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

    /**
     * 感情の解禁ルールと評価クラスを使用して、感情が解禁されることを確認するテスト
     */
    public function test_unlocked_by_rule_and_evaluator()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::HAPPY;
        $this->ruleRepository
            ->shouldReceive('getByEmotion')
            ->andReturnUsing(function ($e) {
                return $e === EmotionState::HAPPY
                    ? new EmotionUnlockRule(EmotionState::HAPPY, 'post_count', 10, null)
                    : null;
            });

        $this->evaluator
            ->shouldReceive('isUnlocked')
            ->with(Mockery::type(User::class), Mockery::type(EmotionUnlockRule::class))
            ->andReturnTrue();
        $statuses = $this->service->buildEmotionStatuses($user, 3);

        $dto = $statuses->firstWhere('key', $emotion->value);
        $this->assertTrue($dto->unlocked);
        $this->assertSame('post_count', $dto->unlockType);
        $this->assertSame(10, $dto->threshold);
    }

    /**
     * 既に解禁済みの感情はルールや評価クラスの影響を受けないことを確認するテスト
     */
    public function test_already_unlocked_overrides_rule_and_evaluator()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::CALM;

        $user->unlockedEmotions()->create([
            'user_id' => $user->id,
            'emotion_state' => $emotion->value,
            'unlocked_at' => now(),
        ]);

        $this->ruleRepository->shouldReceive('getByEmotion')->andReturn(
            new EmotionUnlockRule($emotion, 'post_count', 999, null)
        );
        $this->evaluator->shouldReceive('isUnlocked')->andReturnFalse();

        $statuses = $this->service->buildEmotionStatuses($user, 0);
        $dto = $statuses->firstWhere('key', $emotion->value);

        $this->assertTrue($dto->unlocked);
    }

    public function test_progress_calculation_post_count()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::SAD; // なんでもOK

        $rule = new EmotionUnlockRule(
            $emotion,
            'post_count',
            5,
            null
        );

        $this->ruleRepository
            ->shouldReceive('getByEmotion')
            ->andReturnUsing(fn($e) => $e === $emotion ? $rule : null);

        // まだ未解禁の想定なので false を返す
        $this->evaluator
            ->shouldReceive('isUnlocked')
            ->with(Mockery::type(User::class), Mockery::type(\App\Rules\EmotionUnlockRule::class))
            ->andReturnFalse();
        // postCount=3 → remaining 2
        $statuses = $this->service->buildEmotionStatuses($user, 3);

        $dto = $statuses->firstWhere('key', $emotion->value);
        $this->assertFalse($dto->unlocked);
        $this->assertSame(3, $dto->currentCount);
        $this->assertSame(2, $dto->remaining);
        $this->assertSame('post_count', $dto->unlockType);
        $this->assertSame(5, $dto->threshold);
    }

    /**
     * 投稿数が条件以上の場合にremainingが0よりマイナスにならないテスト
     */
    public function test_post_count_remaining_is_clamped_to_zero()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::SAD;

        $rule = new EmotionUnlockRule($emotion, 'post_count', 5, null);

        $this->ruleRepository
            ->shouldReceive('getByEmotion')
            ->andReturnUsing(fn($e) => $e === $emotion ? $rule : null);

        $this->evaluator
            ->shouldReceive('isUnlocked')
            ->andReturnFalse();

        $statuses = $this->service->buildEmotionStatuses($user, 10);
        $dto = $statuses->firstWhere('key', $emotion->value);

        $this->assertSame(10, $dto->currentCount);
        $this->assertSame(0,  $dto->remaining);
    }

    /**
     * 感情の解禁ルールがベース感情を使用している場合、正しく進捗が計算されることを確認するテスト
     */
    public function test_progress_base_emotion_rule_without_base_emotion_is_safe()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::ANGRY;

        $rule = new EmotionUnlockRule(
            $emotion,
            'base_emotion',
            3,
            null // ← baseEmotion なし
        );

        $this->ruleRepository
            ->shouldReceive('getByEmotion')
            ->andReturnUsing(fn($e) => $e === $emotion ? $rule : null);

        $this->evaluator
            ->shouldReceive('isUnlocked')
            ->with(Mockery::type(User::class), Mockery::type(EmotionUnlockRule::class))
            ->andReturnFalse();

        $statuses = $this->service->buildEmotionStatuses($user, 0);

        $dto = $statuses->firstWhere('key', $emotion->value);
        $this->assertFalse($dto->unlocked);
        $this->assertSame(0, $dto->currentCount);
        $this->assertNull($dto->remaining);       // baseEmotionが無ければ安全にnull
        $this->assertNull($dto->baseEmotion);
        $this->assertSame('base_emotion', $dto->unlockType);
        $this->assertSame(3, $dto->threshold);
    }


    /**
     * ベース感情の条件タイプで、投稿数が閾値以上でも感情タイプでの投稿数が足りない場合に、
     * 正しく進捗が計算されることを確認するテスト
     */
    public function test_progress_base_emotion_counts_remaining()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::GRATEFUL;

        $rule = new EmotionUnlockRule(
            $emotion,
            'base_emotion',
            2,
            EmotionState::HAPPY   // ← HAPPY をベースにカウント
        );

        // ベース感情(HAPPY)の日記を1件だけ用意 → remaining = 1
        $diary = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->create([
            'diary_id'       => $diary->id,
            'emotion_state'  => EmotionState::HAPPY->value,
        ]);

        $this->ruleRepository
            ->shouldReceive('getByEmotion')
            ->andReturnUsing(fn($e) => $e === $emotion ? $rule : null);

        $this->evaluator
            ->shouldReceive('isUnlocked')
            ->with(Mockery::type(User::class), Mockery::type(EmotionUnlockRule::class))
            ->andReturnFalse();

        $statuses = $this->service->buildEmotionStatuses($user, 999);

        $dto = $statuses->firstWhere('key', $emotion->value);
        $this->assertFalse($dto->unlocked);
        $this->assertSame(1, $dto->currentCount); // HAPPY 1件ぶん
        $this->assertSame(1, $dto->remaining);    // 閾値2 - 現在1 = 1
        $this->assertSame('base_emotion', $dto->unlockType);
        $this->assertSame(2, $dto->threshold);
    }

    /**
     * ベース感情の条件タイプの場合に、ベース感情の投稿数が条件数を超過したときに
     * remainingが0よりマイナスにならないことを確認するテスト
     */
    public function test_base_emotion_remaining_is_clamped_to_zero()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::GRATEFUL;
        $rule = new EmotionUnlockRule($emotion, 'base_emotion', 2, EmotionState::HAPPY);

        // HAPPYを2件以上作る
        $d1 = Diary::factory()->create(['user_id' => $user->id]);
        $d2 = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->create(['diary_id' => $d1->id, 'emotion_state' => EmotionState::HAPPY->value]);
        EmotionLog::factory()->create(['diary_id' => $d2->id, 'emotion_state' => EmotionState::HAPPY->value]);

        $this->ruleRepository->shouldReceive('getByEmotion')->andReturnUsing(fn($e) => $e === $emotion ? $rule : null);
        $this->evaluator->shouldReceive('isUnlocked')->andReturnFalse();

        $statuses = $this->service->buildEmotionStatuses($user, 0);
        $dto = $statuses->firstWhere('key', $emotion->value);

        $this->assertSame(2, $dto->currentCount);
        $this->assertSame(0, $dto->remaining);
    }


    /**
     * 最近の感情スコアが正しく取得できることを確認するテスト
     */
    public function test_returns_recent_emotion_counts_array()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->create(['user_id' => $user->id]);

        // 30日以内に2件：HAPPY と SAD を1件ずつ
        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::HAPPY->value,
            'created_at' => Carbon::now()->subDays(5),
        ]);

        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::SAD->value,
            'created_at' => Carbon::now()->subDays(2),
        ]);

        $scores = $this->service->getRecentEmotionScores($user, 30);

        $this->assertIsArray($scores);
        $this->assertNotEmpty($scores);

        // 総和は 2 件のはず（ベース感情が同じなら合算される）
        $this->assertSame(2, array_sum($scores));

        // 0件のベースも含まれる想定なので、総要素数は「ベース感情の種類数」と一致
        $baseCount = collect(\App\Enums\EmotionState::cases())
            ->map->baseCategory()
            ->filter()        // null除外
            ->unique()
            ->count();

        $this->assertCount($baseCount, $scores);
    }

    /**
     * 範囲外データ・他ユーザーは除外されていることを確認するテスト
     */
    public function test_recent_scores_excludes_out_of_range_and_other_users()
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $d1 = Diary::factory()->create(['user_id' => $user->id]);
        $d2 = Diary::factory()->create(['user_id' => $other->id]);

        // 範囲内（計上）
        EmotionLog::factory()->create([
            'diary_id' => $d1->id,
            'emotion_state' => EmotionState::HAPPY->value,
            'created_at' => Carbon::now()->subDays(3),
        ]);
        // 範囲外（除外）
        EmotionLog::factory()->create([
            'diary_id' => $d1->id,
            'emotion_state' => EmotionState::SAD->value,
            'created_at' => Carbon::now()->subDays(90),
        ]);
        // 別ユーザー（除外）
        \App\Models\EmotionLog::factory()->create([
            'diary_id' => $d2->id,
            'emotion_state' => EmotionState::HAPPY->value,
            'created_at' => Carbon::now()->subDays(1),
        ]);

        $scores = $this->service->getRecentEmotionScores($user, 30);
        $this->assertSame(1, array_sum($scores));
    }

    /**
     * ルールと評価クラスがない場合に、感情が解禁されず進捗が0のままであることを確認するテスト
     */
    public function test_no_rule_and_evaluator_false_results_in_locked_with_no_progress()
    {
        $user = User::factory()->create();
        $emotion = EmotionState::CALM;

        $this->ruleRepository->shouldReceive('getByEmotion')->andReturn(null);
        $this->evaluator->shouldReceive('isUnlocked')->andReturnFalse();

        $statuses = $this->service->buildEmotionStatuses($user, 100);
        $dto = $statuses->firstWhere('key', $emotion->value);

        $this->assertFalse($dto->unlocked);
        $this->assertSame(0, $dto->currentCount);
        $this->assertNull($dto->remaining);
    }



    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}

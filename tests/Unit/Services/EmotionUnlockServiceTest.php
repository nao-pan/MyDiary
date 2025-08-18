<?php

namespace Tests\Unit\Services;

use App\Enums\EmotionState;
use App\Models\Diary;
use App\Models\UnlockedEmotion;
use App\Models\User;
use App\Rules\UnlockRuleRepository;
use App\Services\EmotionUnlockService;
use App\Services\UnlockEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmotionUnlockServiceTest extends TestCase
{
    use RefreshDatabase;

    protected EmotionUnlockService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $ruleRepository = new UnlockRuleRepository;
        $evaluator = new UnlockEvaluator;
        $this->service = new EmotionUnlockService($evaluator, $ruleRepository);
    }

    /**
     * 初期解禁の感情が登録されないことを検証するテスト
     */
    public function test_initially_unlocked_emotions_are_not_stored()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->withEmotionLog(EmotionState::HAPPY)->create([
            'user_id' => $user->id,
        ]);

        $this->service->checkAndUnlock($diary);

        // HAPPY感情が解禁されていないことを検証
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::HAPPY->value,
        ]);
        // 初期解禁感情は登録されないため、GRATEFULも確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::GRATEFUL->value,
        ]);
    }

    /**
     * すでに解禁済みの感情が再度登録されないことを検証するテスト
     */
    public function test_already_unlocked_emotion_is_not_recreated()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->withEmotionLog(EmotionState::HAPPY)->create([
            'user_id' => $user->id,
        ]);
        UnlockedEmotion::factory()->grateful()->create([
            'user_id' => $user->id,
            'diary_id' => $diary->id,
        ]);

        $diaries = Diary::factory()
            ->count(19)
            ->withEmotionLog(EmotionState::HAPPY)
            ->create(['user_id' => $user->id]);

        $targetDiary = $diaries->last();

        // 実行
        $this->service->checkAndUnlock($targetDiary);

        // 確認：GRATEFULは1件だけ（再登録されていない）
        $this->assertEquals(
            1,
            UnlockedEmotion::where('user_id', $user->id)
                ->where('emotion_state', EmotionState::GRATEFUL->value)
                ->count()
        );
    }

    /**
     * 投稿数が閾値に達していない場合、感情が解禁されないことを検証するテスト
     */
    public function test_emotion_is_not_unlocked_when_base_emotion_count_is_below_threshold()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->withEmotionLog(EmotionState::SAD)->create([
            'user_id' => $user->id,
        ]);
        $this->service->checkAndUnlock($diary);
        // DISAPPOINTED感情は解禁されていないことを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::DISAPPOINTED->value,
        ]);
    }

    /**
     * 投稿数が閾値に達した場合、PROUD感情が解禁されることを検証するテスト
     * PROUD感情はベース感情（HAPPY）の投稿数依存
     */
    public function test_emotion_is_unlocked_when_happy_posts_reach_threshold()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->count(20)->withEmotionLog(EmotionState::HAPPY)->create([
            'user_id' => $user->id,
        ]);

        // 初期状態では解禁されていないことを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::PROUD->value,
        ]);

        // 解禁処理の実行
        $this->service->checkAndUnlock($diary->last());

        // PROUD感情が解禁されていることを確認
        $this->assertDatabaseHas('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::PROUD->value,
        ]);
    }

    /**
     * 別ベース感情の投稿数が閾値に達した場合、MELANCHOLY感情が解禁されないことを検証するテスト
     * MELANCHOLY感情はベース感情（SAD）の投稿数依存
     */
    public function test_emotion_is_not_unlocked_when_ather_emotion_posts_reach_threshold()
    {
        $user = User::factory()->create();
        $diary = Diary::factory()->count(20)->withEmotionLog(EmotionState::HAPPY)->create([
            'user_id' => $user->id,
        ]);

        // 初期状態では解禁されていないことを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::MELANCHOLY->value,
        ]);

        // 解禁処理の実行
        $this->service->checkAndUnlock($diary->last());

        // MELANCHOLY感情が解禁されていることを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::MELANCHOLY->value,
        ]);
    }

    /**
     * 投稿数が閾値に達していない場合、HOPEFUL感情が解禁されないことを検証するテスト
     * HOPEFUL感情は投稿数依存 20回の投稿で解禁される
     */
    public function test_emotion_is_not_unlocked_when_post_count_is_below_threshold()
    {
        $user = User::factory()->create();
        // 19回の投稿
        $diary = Diary::factory()->count(19)->create([
            'user_id' => $user->id,
        ]);

        // 初期状態では解禁されていないことを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::HOPEFUL->value,
        ]);

        // 24回の投稿の場合の解禁処理の実行
        $this->service->checkAndUnlock($diary->last());

        // CONFUSED感情が解禁されていないことを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::HOPEFUL->value,
        ]);
    }

    /**
     * 投稿数が閾値に達していない場合、HOPEFUL感情が解禁されないことを検証するテスト
     * HOPEFUL感情は投稿数依存 25回の投稿で解禁される
     */
    public function test_emotion_is_unlocked_when_posts_reach_threshold()
    {
        $user = User::factory()->create();
        // 10回のHAPPY感情の投稿
        Diary::factory()->count(10)->withEmotionLog(EmotionState::HAPPY)->create([
            'user_id' => $user->id,
        ]);
        // 10回のSAD感情の投稿
        $diary = Diary::factory()->count(10)->withEmotionLog(EmotionState::SAD)->create([
            'user_id' => $user->id,
        ]);

        // 初期状態では解禁されていないことを確認
        $this->assertDatabaseMissing('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::HOPEFUL->value,
        ]);

        // 25回の投稿の場合の解禁処理の実行
        $this->service->checkAndUnlock($diary->last());

        // CONFUSED感情が解禁されたことを確認
        $this->assertDatabaseHas('unlocked_emotions', [
            'user_id' => $user->id,
            'emotion_state' => EmotionState::HOPEFUL->value,
        ]);
    }

    /**
     * 解禁された感情が正しく取得できることを検証するテスト
     */
    public function test_get_unlocked_emotions()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $diary = Diary::factory()->create([
            'user_id' => $user->id,
        ]);
        UnlockedEmotion::factory()->create([
            'user_id' => $user->id,
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::PROUD->value,
        ]);
        $unlockedEmotions = $this->service->getUnlockedEmotions();
        $this->assertContains(EmotionState::PROUD->value, $unlockedEmotions);
        $this->assertCount(1, $unlockedEmotions);
    }
}

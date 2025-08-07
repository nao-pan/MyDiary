<?php

namespace Tests\Unit\Services;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Services\UnlockEvaluator;
use App\Models\User;
use App\Models\Diary;
use App\Models\EmotionLog;
use App\Enums\EmotionState;
use App\Rules\EmotionUnlockRule;
use App\Rules\UnlockRule;
use Tests\TestCase;

class UnlockEvaluatorTest extends TestCase
{
     use RefreshDatabase;

    protected UnlockEvaluator $evaluator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->evaluator = new UnlockEvaluator();
    }

    /**
     * 初期解禁の感情が正しく解禁されることをテスト
     */
    public function test_unlocks_initial_rule()
    {
        $user = User::factory()->create();

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::HAPPY,
            unlockType: 'initial',
        );

        $this->assertTrue($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * 投稿数による解禁が正しく機能することをテスト
     */
    public function test_unlocks_by_post_count()
    {
        $user = User::factory()->create();
        Diary::factory()->count(5)->create(['user_id' => $user->id]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'post_count',
            threshold: 5
        );

        $this->assertTrue($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * ベース感情のカウントによる解禁が正しく機能することをテスト
     */
    public function test_unlocks_by_base_emotion_count()
    {
        $user = User::factory()->create();

        $diary = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::HAPPY->value
        ]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::GRATEFUL,
            unlockType: 'base_emotion',
            baseEmotion: EmotionState::HAPPY,
            threshold: 1
        );

        $this->assertTrue($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ条件による解禁が正しく機能することをテスト
     */
    public function test_unlocks_combo_condition()
    {
        $user = User::factory()->create();
        Diary::factory()->count(4)->create(['user_id' => $user->id]);
        $diary = Diary::factory()->create(['user_id' => $user->id]);

        $user->unlockedEmotions()->createMany([
            [
                'diary_id' => $diary->id,
                'emotion_state' => EmotionState::FUN->value,
                'unlocked_at' => now(),
            ],
            [
                'diary_id' => $diary->id,
                'emotion_state' => EmotionState::RELIEVED->value,
                'unlocked_at' => now(),],
        ]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'post_count', 'threshold' => 5],
                ['type' => 'unlocked_emotions', 'emotions' => ['fun', 'relieved']],
            ]
        );

        $this->assertTrue($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ条件が満たされない場合、解禁されないことをテスト
     */
    public function test_fails_if_combo_condition_not_met()
    {
        $user = User::factory()->create();

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'post_count', 'threshold' => 10],
            ]
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }
}

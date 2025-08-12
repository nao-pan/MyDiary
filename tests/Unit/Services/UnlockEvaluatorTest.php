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
     * 初期解禁ではない場合に解禁されないことをテスト
     */
    public function test_does_not_unlock_initial_rule_for_non_initial()
    {
        $user = User::factory()->create();

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'post_count',
            threshold: 1
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
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
        EmotionLog::factory()->create([
            'diary_id' => $diary->id,
            'emotion_state' => EmotionState::HAPPY->value
        ]);
        $user->unlockedEmotions()->createMany([
            [
                'diary_id' => $diary->id,
                'emotion_state' => EmotionState::FUN->value,
                'unlocked_at' => now(),
            ],
            [
                'diary_id' => $diary->id,
                'emotion_state' => EmotionState::RELIEVED->value,
                'unlocked_at' => now(),
            ],
        ]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'post_count', 'threshold' => 5],
                ['type' => 'unlocked_emotions', 'emotions' => ['fun', 'relieved']],
                ['type' => 'base_emotion', 'baseEmotion' => EmotionState::HAPPY->value, 'threshold' => 1],
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

        /**
     * 投稿数ルール: 閾値未満ならfalse
     */
    public function test_does_not_unlock_by_post_count_when_below_threshold()
    {
        $user = User::factory()->create();
        Diary::factory()->count(2)->create(['user_id' => $user->id]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'post_count',
            threshold: 3
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * ベース感情ルール: 閾値未満ならfalse
     */
    public function test_does_not_unlock_by_base_emotion_when_below_threshold()
    {
        $user = User::factory()->create();

        // HAPPYを1回だけ記録（閾値は2に設定して不達成にする）
        $d1 = Diary::factory()->create(['user_id' => $user->id]);
        EmotionLog::factory()->create([
            'diary_id' => $d1->id,
            'emotion_state' => EmotionState::HAPPY->value,
        ]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::GRATEFUL,
            unlockType: 'base_emotion',
            baseEmotion: EmotionState::HAPPY,
            threshold: 2
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * ベース感情ルール: baseEmotion未指定やthreshold未指定ならfalse
     */
    public function test_does_not_unlock_by_base_emotion_when_params_missing()
    {
        $user = User::factory()->create();

        // baseEmotionなし
        $ruleMissingBase = new EmotionUnlockRule(
            emotion: EmotionState::GRATEFUL,
            unlockType: 'base_emotion',
            baseEmotion: null,
            threshold: 1
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleMissingBase));

        // thresholdなし
        $ruleMissingThreshold = new EmotionUnlockRule(
            emotion: EmotionState::GRATEFUL,
            unlockType: 'base_emotion',
            baseEmotion: EmotionState::HAPPY,
            threshold: null
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleMissingThreshold));
    }

    /**
     * 未対応unlockTypeならfalse（isInitial/isPostCount等どれにも該当しない）
     */
    public function test_does_not_unlock_with_unknown_unlock_type()
    {
        $user = User::factory()->create();

        // EmotionUnlockRule側のisXxx判定が文字比較なら、unknownでどれにも該当せずfalseになる
        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'unknown_type'
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ: 条件にtypeが無い場合はfalse
     */
    public function test_combo_fails_when_condition_type_missing()
    {
        $user = User::factory()->create();

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                // typeキーが無い
                ['threshold' => 1],
            ]
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ: 未対応typeならfalse
     */
    public function test_combo_fails_when_unknown_condition_type()
    {
        $user = User::factory()->create();

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'not_supported', 'foo' => 'bar'],
            ]
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ: post_count条件でthreshold欠落時はfalse
     */
    public function test_combo_fails_when_post_count_threshold_missing()
    {
        $user = User::factory()->create();
        Diary::factory()->count(5)->create(['user_id' => $user->id]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'post_count'], // thresholdなし
            ]
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ: unlocked_emotions条件で配列が空/未指定ならfalse
     */
    public function test_combo_fails_when_unlocked_emotions_missing_or_empty()
    {
        $user = User::factory()->create();

        // emotionsキーなし
        $ruleNoKey = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'unlocked_emotions'],
            ]
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleNoKey));

        // 空配列
        $ruleEmpty = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'unlocked_emotions', 'emotions' => []],
            ]
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleEmpty));
    }

    /**
     * コンボ: unlocked_emotions条件で必要な感情が未解禁ならfalse
     */
    public function test_combo_fails_when_unlocked_emotions_not_all_present()
    {
        $user = User::factory()->create();

        // 片方だけ解禁しておく
        $d = Diary::factory()->create(['user_id' => $user->id]);
        $user->unlockedEmotions()->create([
            'diary_id' => $d->id,
            'emotion_state' => EmotionState::FUN->value,
            'unlocked_at' => now(),
        ]);

        $rule = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'unlocked_emotions', 'emotions' => ['fun', 'relieved']],
            ]
        );

        $this->assertFalse($this->evaluator->isUnlocked($user, $rule));
    }

    /**
     * コンボ: base_emotion条件でbaseEmotion/threshold欠落、またはカウント未達ならfalse
     */
    public function test_combo_fails_when_base_emotion_params_missing_or_below_threshold()
    {
        $user = User::factory()->create();

        // baseEmotionなし
        $ruleMissingBase = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'base_emotion', 'threshold' => 1],
            ]
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleMissingBase));

        // thresholdなし
        $ruleMissingThreshold = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'base_emotion', 'baseEmotion' => EmotionState::HAPPY->value],
            ]
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleMissingThreshold));

        // 閾値未達（HAPPYを0回でthreshold 1）
        $ruleBelow = new EmotionUnlockRule(
            emotion: EmotionState::CONFUSED,
            unlockType: 'combo',
            conditions: [
                ['type' => 'base_emotion', 'baseEmotion' => EmotionState::HAPPY->value, 'threshold' => 1],
            ]
        );
        $this->assertFalse($this->evaluator->isUnlocked($user, $ruleBelow));
    }

}

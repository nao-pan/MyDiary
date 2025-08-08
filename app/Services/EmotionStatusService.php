<?php

namespace App\Services;

use App\Models\EmotionLog;
use App\Models\User;
use App\Enums\EmotionState;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use App\Rules\UnlockRuleRepository;
use App\Services\UnlockEvaluator;
use App\Models\EmotionColor;
use App\Dto\EmotionStatus;

class EmotionStatusService
{
  protected UnlockRuleRepository $ruleRepository;
  protected UnlockEvaluator $evaluator;

  public function __construct(UnlockRuleRepository $ruleRepository, UnlockEvaluator $evaluator)
  {
    $this->ruleRepository = $ruleRepository;
    $this->evaluator = $evaluator;
  }

  /**
   * 感情の解禁状態を返す
   */
  public function buildEmotionStatuses(User $user, int $postCount): Collection
  {
    $unlokedEmotionStates = $user->unlockedEmotions()
      ->pluck('emotion_state')
      ->toArray();

    return collect(EmotionState::cases())->map(function ($emotion) use ($user, $unlokedEmotionStates, $postCount) {
      $rule = $this->ruleRepository->getByEmotion($emotion);

      $isUnlocked = in_array($emotion->value, $unlokedEmotionStates)
        || ($rule && $this->evaluator->isUnlocked($user, $rule));
      
      $threshold = $rule?->threshold;
      $unlockType = $rule?->unlockType;

      $currentCount = 0;
      $remaining = null;
      $baseEmotion = null;
      [$currentCount, $remaining, $baseEmotion] = (!$isUnlocked && $rule)
            ? $this->calculateProgress($user, $rule, $postCount)
            : [0, null, null];
        //　以下のコードはコンボの条件を取り出すものだが、未実装のためコメントアウト
      // if (!$isUnlocked && $rule) {
      //   if ($rule->unlockType === 'combo') {
      //     $results = $this->comboEvaluator->evaluate($user, $rule->conditions);
      //     $isUnlocked = collect($results)->every(fn($r) => $r === true);
      //   }
      // }

      return new EmotionStatus(
        $emotion->value,
        $emotion->label(),
        $emotion->defaultColor(),
        $emotion->textColor(),
        $isUnlocked,
        $threshold,
        $unlockType,
        $currentCount,
        $remaining,
        $baseEmotion?->label(),
        $unlockType === 'initial',
      );
    });
  }

  private function calculateProgress(User $user, object $rule, int $postCount): array
  {
    return match ($rule->unlockType) {
      'post_count' => $this->calculateByPostCount($rule, $postCount),
      'base_emotion' => $this->calculateByBaseEmotion($user, $rule),
      default => [0, null, null]
    };
  }

  private function calculateByPostCount(object $rule, int $postCount): array
  {
    return [$postCount, max(0, $rule->threshold - $postCount), null];
  }

  private function calculateByBaseEmotion(User $user, object $rule): array
  {
    if (!$rule->baseEmotion) {
      return [0, null, null];
    }
    $count = $user->diaries()
      ->whereHas('emotionLog', fn($q) => $q->where('emotion_state', $rule->baseEmotion->value))
      ->count();
    return [$count, max(0, $rule->threshold - $count), $rule->baseEmotion];
  }

  /**
   * 直近の感情スコアを取得
   *
   * @param User $user
   * @param int $days
   * @return array
   */
  public function getRecentEmotionScores(User $user, int $days = 30): array
  {
    $recentEmotionScores = [];
    // 最近のEmotionLogを取得
    $recentLogs = EmotionLog::whereHas('diary', function ($query) use ($user) {
      $query->where('user_id', $user->id);
    })->where('created_at', '>=', Carbon::now()->subDays($days))
      ->get();

    // ベース感情の一覧を取得
    $baseEmotions = collect(EmotionState::cases())
      ->filter(fn($emotion) => $emotion->baseCategory() !== null)
      ->map(fn($emotion) => $emotion->baseCategory())
      ->unique();

    // 各ベース感情のスコアを集計
    foreach ($baseEmotions as $emotion) {
      $count = $recentLogs->filter(
        fn($log) =>
        $log->emotion_state->baseCategory() === $emotion
      )->count();

      $recentEmotionScores[] = $count;
    }

    return $recentEmotionScores;
  }
}

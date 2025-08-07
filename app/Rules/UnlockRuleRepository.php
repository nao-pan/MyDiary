<?php

namespace App\Rules;

use Illuminate\Support\Facades\File;
use App\Enums\EmotionState;

class UnlockRuleRepository
{
  /** @var UnlockRule[] */
    protected array $rules;

    public function __construct()
    {
        $this->rules = collect(json_decode(
          File::get(resource_path('data/emotion_unlock_rules.json')),
          true
        ))->map(function ($data){
            return new EmotionUnlockRule(
                EmotionState::from($data['emotion']),
                $data['unlockType'],
                $data['threshold'] ?? null,
                isset($data['baseEmotion']) ? EmotionState::from($data['baseEmotion']) : null,
                $data['conditions'] ?? [],
                $data['isSecret'] ?? false,
                $data['hint'] ?? null
            );
        })->all();
    }

    public function all(): array
    {
      return $this->rules;
    }

    public function getByEmotion(EmotionState $emotion): ?EmotionUnlockRule
    {
      return collect($this->rules)->firstWhere('emotion', $emotion);
    }
}
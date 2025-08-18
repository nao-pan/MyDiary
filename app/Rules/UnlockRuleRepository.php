<?php

namespace App\Rules;

use App\Enums\EmotionState;
use Illuminate\Support\Facades\File;

class UnlockRuleRepository
{
    /** @var UnlockRule[] */
    protected array $rules;

    public function __construct()
    {
        // JSONファイルからルールを読み込む
        $json = File::get(resource_path('data/emotion_unlock_rules.json'));

        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException('Invalid JSON in emotion_unlock_rules.json');
        }
        $this->rules = collect($decoded)->map(function ($data) {
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

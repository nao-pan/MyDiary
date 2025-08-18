<?php

namespace App\Rules;

use App\Enums\EmotionState;

class EmotionUnlockRule
{
    public function __construct(
        public EmotionState $emotion,
        public string $unlockType,        // 'post_count' | 'base_emotion' | 'combo' | 'secret'
        public ?int $threshold = null,
        public ?EmotionState $baseEmotion = null,
        public array $conditions = [], // combo用
        public bool $isSecret = false,
        public ?string $hint = null,     // UIにヒントを出す場合
    ) {}

    public function isCombo(): bool
    {
        return $this->unlockType === 'combo';
    }

    public function isInitial(): bool
    {
        return $this->unlockType === 'initial';
    }

    public function isPostCount(): bool
    {
        return $this->unlockType === 'post_count';
    }

    public function isBaseEmotion(): bool
    {
        return $this->unlockType === 'base_emotion';
    }

    public function toArray(): array
    {
        return [
            'emotion' => $this->emotion->value,
            'unlockType' => $this->unlockType,
            'threshold' => $this->threshold,
            'baseEmotion' => $this->baseEmotion?->value,
            'conditions' => $this->conditions,
            'isSecret' => $this->isSecret,
            'hint' => $this->hint,
        ];
    }
}

<?php

namespace App\Dto;

class EmotionStatus implements \JsonSerializable
{
    public function __construct(
        public string $key,
        public string $label,
        public string $defaultColor,
        public string $textColor,
        public bool $unlocked,
        public ?int $threshold,
        public ?string $unlockType,
        public int $currentCount,
        public ?int $remaining,
        public ?string $baseEmotion,
        public bool $isInitial
    ) {}

    public function toArray(): array
    {
      return [
          'key' => $this->key,
          'label' => $this->label,
          'color' => $this->defaultColor,// 注：現状デフォルトカラーしか使用しないため
          'textColor' => $this->textColor,
          'unlocked' => $this->unlocked,
          'threshold' => $this->threshold,
          'unlockType' => $this->unlockType,
          'currentCount' => $this->currentCount,
          'remaining' => $this->remaining,
          'baseEmotion' => $this->baseEmotion,
          'isInitial' => $this->isInitial,
      ];
    }

        public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}

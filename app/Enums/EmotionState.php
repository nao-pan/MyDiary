<?php

namespace App\Enums;

enum EmotionState: string
{
    // ベーシック感情
    case HAPPY = 'happy';
    case SAD = 'sad';
    case ANGRY = 'angry';
    case CALM = 'calm';
    case NEUTRAL = 'neutral';
    case FEAR = 'fear';
    case FUN = 'fun';


        // アドバンス感情
    case EXCITED = 'excited';
    case ANXIOUS = 'anxious';
    case CONFUSED = 'confused';
    case GRATEFUL = 'grateful';
    case HOPEFUL = 'hopeful';
    case MELANCHOLY = 'melancholy';

    /**
     * 表示用のラベル
     */
    public function label(): string
    {
        return match ($this) {
            self::FUN => '楽しい',
            self::HAPPY => '嬉しい',
            self::SAD => '悲しい',
            self::ANGRY => '怒り',
            self::CALM => '落ち着いている',
            self::NEUTRAL => 'フラット',
            self::FEAR => '恐れ',
            self::EXCITED => '興奮',
            self::ANXIOUS => '不安',
            self::CONFUSED => '混乱',
            self::GRATEFUL => '感謝',
            self::HOPEFUL => '希望',
            self::MELANCHOLY => '憂鬱',
            default => '未知の感情',
        };
    }

    /**
     * 表示用の色
     */
    public function color(): string
    {
        return match ($this) {
            self::FUN => '#FFEB3B', // Yellow
            self::HAPPY => '#4CAF50', // Green
            self::SAD => '#2196F3', // Blue
            self::ANGRY => '#F44336', // Red
            self::CALM => '#9E9E9E', // Grey
            self::NEUTRAL => '#FFFFFF', // White
            self::FEAR => '#9C27B0', // Purple
            self::EXCITED => '#FF9800', // Orange
            self::ANXIOUS => '#FF5722', // Deep Orange
            self::CONFUSED => '#3F51B5', // Indigo
            self::GRATEFUL => '#8BC34A', // Light Green
            self::HOPEFUL => '#CDDC39', // Lime
            self::MELANCHOLY => '#607D8B', // Blue Grey
            default => '#000000', // Black for unknown emotions
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::HAPPY, self::CALM, self::NEUTRAL, self::GRATEFUL, self::HOPEFUL => '#000000', // Black for light colors
            default => '#FFFFFF', // White for dark colors
        };
    }

    public function unlockThreshold(): ?int
    {
        return match ($this) {
            self::HAPPY, self::SAD, self::ANGRY, self::FEAR => null, // 初期解禁
            self::GRATEFUL => 3,
            self::MELANCHOLY => 7,
            default => 30, // その他の感情は30回
        };
    }

    public function unlockBaseEmotion(): ?self
    {
        return match ($this) {
            self::GRATEFUL => self::HAPPY,
            self::MELANCHOLY => self::SAD,
            default => null,
        };
    }

    public function isInitiallyUnlocked(): bool
    {
        return is_null($this->unlockThreshold());
    }

    /**
     * 全てのケースの値を取得
     */
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}

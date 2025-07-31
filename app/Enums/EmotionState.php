<?php

namespace App\Enums;

enum EmotionState: string
{
    // ベーシック感情
    case HAPPY = 'happy';
    case SAD = 'sad';
    case ANGRY = 'angry';
    case FEAR = 'fear';
    case SURPURISED = 'surprised';
    case DISGUSTED = 'disgusted';

        // Happy系統
    case FUN = 'fun';
    case PROUD = 'proud';
    case GRATEFUL = 'grateful';
    case RELIEVED = 'relieved';

        // Sad系統
    case DISAPPOINTED = 'disappointed';
    case LONELY = 'lonely';
    case HEARTBROKEN = 'heartbroken';
    case MELANCHOLY = 'melancholy';

        // angry系統
    case IRRITATED = 'irritated';
    case FRUSTRATED = 'frustrated';
    case FED_UP = 'fed_up';

        // fear系統
    case NERVOUS = 'nervous';
    case WORRIED = 'worried';
    case ANXIOUS = 'anxious';

        // surprised系統
    case SHAKEN = 'shaken';
    case STUNNED = 'stunned';
    case EXCITED = 'excited';

        // disgust系統
    case REJECTING = 'rejecting';
    case FED_UP_AGAIN = 'fed_up_again';
    case NAUSEATED = 'nauseated';


    case CALM = 'calm';
    case NEUTRAL = 'neutral';
    case HOPEFUL = 'hopeful';



        // アドバンス感情


    case CONFUSED = 'confused';




    /**
     * 表示用のラベル
     */
    public function label(): string
    {
        return match ($this) {
            // ベーシック感情
            self::HAPPY => '嬉しい',
            self::SAD => '悲しい',
            self::ANGRY => 'イライラ',
            self::SURPURISED => '驚いた',
            self::DISGUSTED => '嫌だった',
            self::FEAR => '不安',
            // Happy系統
            self::FUN => '楽しい',
            self::PROUD => '誇らしい',
            self::GRATEFUL => '感謝',
            self::RELIEVED => '安心',
            // Sad系統
            self::DISAPPOINTED => 'がっかり',
            self::LONELY => '寂しい',
            self::HEARTBROKEN => '失恋',
            self::MELANCHOLY => '憂鬱',
            // Angry系統
            self::IRRITATED => 'イライラ',
            self::FRUSTRATED => 'フラストレーション',
            self::FED_UP => 'うんざり',
            // Fear系統
            self::NERVOUS => '緊張',
            self::WORRIED => '心配',
            self::ANXIOUS => '不安',
            // Surprised系統
            self::SHAKEN => '動揺',
            self::STUNNED => '呆然',
            // Disgust系統
            self::REJECTING => '拒否感',
            self::FED_UP_AGAIN => '再びうんざり',
            self::NAUSEATED => '吐き気',

            // その他
            self::CALM => '落ち着いている',
            self::NEUTRAL => 'フラット',
            self::EXCITED => '興奮',
            self::CONFUSED => '混乱',
            self::HOPEFUL => '希望',
            default => '未知の感情',
        };
    }

    /**
     * ベースカテゴリを取得
     * 各感情がどのベースカテゴリに属するかを返す
     */
    public function baseCategory(): ?self
    {
        return match ($this) {
            // ベースカテゴリ自体は自分を返す
            self::HAPPY, self::SAD, self::ANGRY, self::FEAR, self::SURPURISED, self::DISGUSTED => $this,

            // 各カテゴリごとの所属
            self::FUN, self::PROUD, self::GRATEFUL, self::RELIEVED => self::HAPPY,
            self::DISAPPOINTED, self::LONELY, self::HEARTBROKEN, self::MELANCHOLY => self::SAD,
            self::IRRITATED, self::FRUSTRATED, self::FED_UP => self::ANGRY,
            self::NERVOUS, self::WORRIED, self::ANXIOUS => self::FEAR,
            self::SHAKEN, self::STUNNED, self::EXCITED => self::SURPURISED,
            self::REJECTING, self::FED_UP_AGAIN, self::NAUSEATED => self::DISGUSTED,

            // どこにも属さない感情はnull（フラット、混乱など）
            default => null,
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
            default => '#807d7dff', // Black for unknown emotions
        };
    }

    public function unlockType(): string
    {
        return match ($this) {
            self::GRATEFUL => 'base_emotion',
            self::MELANCHOLY => 'base_emotion',
            self::ANXIOUS => 'base_emotion',
            default => 'post_count', // その他の感情は投稿数に依存
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
            self::HAPPY, self::SAD, self::ANGRY, self::FEAR, self::FUN => null, // 初期解禁
            // happy系統
            self::PROUD => 10, // 誇らしいは10回
            self::GRATEFUL => 20, // 感謝は20回
            self::RELIEVED => 15, // 安心は15回
            // sad系統
            self::DISAPPOINTED => 5, // がっかりは5回
            self::LONELY => 10, // 寂しいは10回
            self::HEARTBROKEN => 15, // 失恋は15回
            self::MELANCHOLY => 20, // 憂鬱は20回
            // angry系統
            self::IRRITATED => 5, // イライラは5回
            self::FRUSTRATED => 10, // フラストレーションは10回
            self::FED_UP => 15, // うんざりは15回
            // fear系統
            self::NERVOUS => 5, // 緊張は5回
            self::WORRIED => 10, // 心配は10回
            self::ANXIOUS => 15, // 不安は15回
            // surprised系統
            self::SHAKEN => 5, // 動揺は5回
            self::STUNNED => 10, // 呆然は10回
            self::EXCITED => 15, // 興奮は15回
            // disgust系統
            self::REJECTING => 5, // 拒否感は5回
            self::FED_UP_AGAIN => 10, // 再びうんざりは10回
            self::NAUSEATED => 15, // 吐き気は15回
            // その他の感情
            self::CALM => null, // 落ち着いているは初期解禁
            self::NEUTRAL => null, // フラットは初期解禁
            self::CONFUSED => 25, // 混乱は25回
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

    /**
     * ベース感情を返す
     */
    public static function baseEmotions(): array
    {
        return [
            self::HAPPY,
            self::SAD,
            self::ANGRY,
            self::FEAR,
            self::SURPURISED,
            self::DISGUSTED
        ];
    }
}

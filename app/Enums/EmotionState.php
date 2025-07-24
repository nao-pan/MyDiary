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
        return match($this) {
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

}

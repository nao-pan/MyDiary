<?php

namespace App\Services;

use App\Models\Diary;
use App\Models\EmotionLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Enums\EmotionState;
use App\Models\EmotionColor;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DiaryService
{
    public function createWithEmotion(array $data): Diary
    {
        return DB::transaction(function () use ($data) {
            // 日記の作成
            $diary = Diary::create([
                'user_id' => Auth::id(),
                'title' => $data['title'],
                'content' => $data['content'],
                'happinness_score' => $data['happinness_score'] ?? null, // ハピネススコアはオプション?
            ]);

            // 感情ログの作成
            EmotionLog::create([
                'diary_id' => $diary->id,
                'emotion_state' => $data['emotion_state'],
                'emotion_score' => $data['emotion_score'],
                'created_at' => now(),
            ]);

            return $diary;
        });
    }

    public function getCalendarEventsForUser(User $user): Collection
{
    $startOfMonth = Carbon::now()->startOfMonth();
    $endOfMonth = Carbon::now()->endOfMonth();

    $logs = EmotionLog::with('diary')
        ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->whereHas('diary', fn ($query) => $query->where('user_id', $user->id))
        ->get();

    $customColors = EmotionColor::where('user_id', $user->id)
        ->pluck('color_code', 'emotion_state');

    return $logs->map(function ($log) use ($customColors) {
        $enum = $log->emotion_state;
        $date = $log->created_at->format('Y-m-d');
        $textColor = $enum->textColor();

        return [
            [
                'title' => '',
                'start' => $date,
                'display' => 'background',
                'color' => $customColors[$enum->value] ?? $enum->defaultColor(),
            ],
            [
                'title' => Str::limit($log->diary->title ?? '', 12),
                'start' => $date,
                'url' => route('diary.show', $log->diary->id),
                'color' => $customColors[$enum->value] ?? $enum->defaultColor(),
                'textColor' => $textColor,
            ]
        ];
    })->flatten(1);
}

public function getRecentDiaries(User $user, int $limit = 5)
{
    return Diary::where('user_id', $user->id)
        ->latest('created_at')
        ->take($limit)
        ->get();
}

}

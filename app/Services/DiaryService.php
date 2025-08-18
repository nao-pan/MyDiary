<?php

namespace App\Services;

use App\Models\Diary;
use App\Models\EmotionColor;
use App\Models\EmotionLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DiaryService
{
    public function __construct(
        protected EmotionLogService $emotionLogService
    ) {}

    public function createWithEmotion(User $user, array $data): Diary
    {
        return DB::transaction(function () use ($user, $data) {
            // 日記の作成
            $diary = Diary::create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'content' => $data['content'],
                'happiness_score' => $data['happiness_score'] ?? null, // ハピネススコアはオプション?
            ]);

            // 感情ログの作成
            $this->emotionLogService->create([
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
                ],
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

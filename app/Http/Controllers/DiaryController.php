<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\DiaryService;
use App\Models\EmotionLog;
use App\Models\EmotionColor;
use Carbon\Carbon;
use App\Enums\EmotionState;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DiaryController extends Controller
{
    protected $diaryService;

    public function __construct(DiaryService $diaryService)
    {
        $this->diaryService = $diaryService;
        $this->middleware('auth'); // 認証ミドルウェアを適用
    }

    public function index()
    {
        $user = Auth::user();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 月ごとの感情ログを取得
        $logs = EmotionLog::with('diary')
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereHas('diary', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get();

        // ユーザー設定の感情色を取得
        $customColors = EmotionColor::where('user_id', $user->id)
            ->pluck('color_code', 'emotion_state');

        $calendarEvents = $logs->map(function ($log) use ($customColors) {
            $enum = $log->emotion_state;
            $date = $log->created_at->format('Y-m-d');
            $textColor = $enum->textColor(); // Enumからテキストカラーを取得
            return [
                // 背景色を設定
                [
                'title' => '',
                'start' => $date,
                'display' => 'background',
                'color' => $customColors[$log->emotion_state->value] ?? $enum->color(), // デフォルトの色を使用
                ],
                // 日記のタイトルとリンクを設定
                [
                    'title' => Str::limit($log->diary->title ?? '',12),
                    'start' => $date,
                    'url' => route('diary.show', $log->diary->id),
                    'color' => $customColors[$log->emotion_state->value] ?? $enum->color(), // デフォルトの色を使用
                    'textColor' => $textColor,
                ]
            ];
        })->flatten(1);
        // 最近の日記（新しい順）5件
        $recentDiaries = Diary::where('user_id', $user->id)
            ->latest('created_at')
            ->take(5)
            ->get();


        return view('diary.index', [
            'calendarEvents' => $calendarEvents,
            'recentDiaries' => $recentDiaries,
        ]);

    }

    // 新しい日記エントリ作成フォームを表示する処理
    public function create(): View
    {
        return view('diary.create');
    }

    // 日記エントリを保存する処理
    public function store(Request $request): RedirectResponse
    {
        // タイトルと内容のバリデーション
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'emotion_state' => ['required', Rule::in(EmotionState::values())], // 感情状態のバリデーション
        ]);

        $this->diaryService->createWithEmotion($validatedData);
        return redirect()->route('diary.index')->with('success', '日記を投稿しました。');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id)
    {
        // 関連データをまとめて取得
        $diary = Diary::with([
            'user',
            'emotionLog'
        ])->findOrFail($id);

        // Enum変換などの処理（EmotionState表示用）
        $emotionLabel = $diary->emotionLog->emotion_state->label(); // 例: EmotionState::from($diary->emotion_state)->label()

        return view('diary.show', [
            'diary' => $diary,
            'emotionLabel' => $emotionLabel,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Diary $diary)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Diary $diary)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Diary $diary)
    {
        //
    }
}

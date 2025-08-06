<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Services\DiaryService;
use App\Services\EmotionUnlockService;
use App\Enums\EmotionState;

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

        $calendarEvents = $this->diaryService->getCalendarEventsForUser($user);
        $recentDiaries = $this->diaryService->getRecentDiaries($user, 5);

        return view('diary.index', compact('calendarEvents', 'recentDiaries'));
    }

    // 新しい日記エントリ作成フォームを表示する処理
    public function create(EmotionUnlockService $unlockservice): View
    {
        $unlockedEmotionKeys = $unlockservice->getUnlockedEmotions();

        $availableEmotions = array_filter(EmotionState::cases(), function ($state) use ($unlockedEmotionKeys) {
        return $state->isInitiallyUnlocked() || in_array($state->value, $unlockedEmotionKeys);
    });
        return view('diary.create',[
            'emotionStates' => $availableEmotions,
        ]);
    }

    // 日記エントリを保存する処理
    public function store(Request $request): RedirectResponse
    {
        // タイトルと内容のバリデーション
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'emotion_state' => ['required'], // 感情状態のバリデーション
            'emotion_score' => 'required|numeric|in:0.2,0.4,0.6,0.8,1.0', // 感情スコアのバリデーション
            'happiness_score' => 'nullable|integer|min:1|max:10', // ハピネススコアのバリデーション
        ]);

        $diary = $this->diaryService->createWithEmotion(Auth::user(), $validatedData);
        // 感情のアンロックチェック
        $emotionUnlockService = new EmotionUnlockService();
        $emotionUnlockService->checkAndUnlock($diary);
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

        $this->authorize('view', $diary);

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

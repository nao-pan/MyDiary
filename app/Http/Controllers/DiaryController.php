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
        // ログインユーザーの投稿のみ取得（新しい順）
        $diaries = Diary::where('user_id', Auth::id())->latest()->get(10);

        return view('diary.index', compact('diaries'));
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
            'aiFeedback',
            'tags',
            'emotionLogs'
        ])->findOrFail($id);

        // Enum変換などの処理（EmotionState表示用）
        $emotionLabel = $diary->emotion_state->label(); // 例: EmotionState::from($diary->emotion_state)->label()

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

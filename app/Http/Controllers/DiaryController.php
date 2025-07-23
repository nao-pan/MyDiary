<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiaryController extends Controller
{
  public function __construct()
  {
    $this->middleware('auth');
  }

  public function index()
  {
    // ログインユーザーの投稿のみ取得（新しい順）
    $diaries = Diary::where('user_id', Auth::id())->latest()->get();

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

    $data = $validatedData;
    $data['user_id'] = Auth::id(); // 現在のユーザーIDを設定
    Diary::create($data);
    return redirect()->route('diary.index')->with('success', '日記を投稿しました。');
  }
}

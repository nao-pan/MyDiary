@extends('layouts.app')

@section('content')
@php
    $emotion = $diary->emotionLog->emotion_state;
    $score = $diary->emotionLog->score;
@endphp
<div class="container py-4">
    <h1 class="text-2xl font-bold mb-4 flex items-center gap-2">
      📝 {{ $diary->title }}

    @if ($emotion)
        <span class="text-sm font-semibold px-2 py-1 rounded" style="background-color: {{ $emotion->color() }}; color: {{ $emotion->textColor()}};">
            {{ $emotion->label() }}
        </span>
    @endif
    </h1>

    <p class="text-gray-500 mb-2">投稿日: {{ $diary->created_at->format('Y年m月d日') }}</p>
    <p class="text-gray-500 mb-4">作成者: {{ $diary->user->nickname }}</p>

    {{-- 感情ログの表示 --}}

    <div class="mt-4 space-y-2">
        {{-- 幸福度スコア --}}
        @if (!is_null($diary->happiness_score))
            <p><strong>🌟 幸福度スコア:</strong> {{ $diary->happiness_score }}/10</p>
        @endif

        {{-- 感情ログ（1件のみ） --}}
        @if ($diary->emotionLog)
            <p>
                <strong>🧠 感情:</strong> {{ $diary->emotionLog->emotion_state }} /
                <strong>強さ:</strong> {{ number_format($diary->emotionLog->emotion_score, 1) }}
            </p>
        @endif
    </div>

    <div class="mb-6">
        <p class="whitespace-pre-line">{{ $diary->content }}</p>
    </div>

    <a href="{{ route('diary.index') }}" class="text-blue-500 hover:underline">← 一覧に戻る</a>
</div>
@endsection

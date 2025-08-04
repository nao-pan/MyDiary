@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-4">
        <h1 class="text-4xl font-bold font-elegant text-center mb-12">📅 {{ Auth::user()->nickname }}さんの 日記カレンダー</h1>



    {{-- カレンダー本体 --}}
    <div id="calendar" class="w-full max-w-3xl mx-auto h-[700px] mb-6 bg-white bg-opacity-80 backdrop-blur-md shadow-md border border-rose-100 rounded-xl px-6 py-12" data-events='@json($calendarEvents)'></div>
</div>
<div class="text-right max-w-4xl mx-auto px-4 py-4">
        {{-- 新しい日記を書くボタン --}}
        <a href="{{ route('diary.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold font-elegant px-4 py-2 rounded">
            ➕ 新しい日記を書く
        </a>
</div>
    {{-- 最近の日記の一覧表示（任意） --}}
    @foreach ($recentDiaries as $diary)
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold font-noto">{{ $diary->title }}</h2>
            <p class="text-sm text-gray-600 font-noto">{{ $diary->created_at->format('Y/m/d') }}</p>
            <p>{{ Str::limit($diary->content, 100) }}</p>
            <a href="{{ route('diary.show', $diary->id) }}" class="text-blue-500 hover:underline">続きを読む</a>
        </div>
    @endforeach
</div>
@endsection

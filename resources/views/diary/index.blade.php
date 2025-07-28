@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-4">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">📅 {{ Auth::user()->nickname }} 日記カレンダー</h1>
        <a href="{{ route('diary.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded">
            ➕ 新しい日記を書く
        </a>
    </div>

    {{-- カレンダー本体 --}}
    <div id="calendar" class="w-full max-w-3xl mx-auto h-[600px] mb-6 bg-white shadow rounded" data-events='@json($calendarEvents)'></div>
</div>

    {{-- 最近の日記の一覧表示（任意） --}}
    @foreach ($recentDiaries as $diary)
        <div class="p-4 border-b">
            <h2 class="text-lg font-bold">{{ $diary->title }}</h2>
            <p class="text-sm text-gray-600">{{ $diary->created_at->format('Y/m/d') }}</p>
            <p>{{ Str::limit($diary->content, 100) }}</p>
            <a href="{{ route('diary.show', $diary->id) }}" class="text-blue-500 hover:underline">続きを読む</a>
        </div>
    @endforeach
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">📓 日記一覧</h1>

    {{-- 投稿メッセージ --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- 投稿があるか確認 --}}
    @if ($diaries->isEmpty())
        <p>まだ日記がありません。</p>
    @else
        <div class="list-group">
            @foreach ($diaries as $diary)
                <a href="{{ route('diary.show', $diary) }}" class="list-group-item list-group-item-action">
                    <h5 class="mb-1">{{ $diary->title }}</h5>
                    <small class="text-muted">{{ $diary->created_at->format('Y年m月d日') }}</small>
                    <p class="mb-1 text-truncate">{{ Str::limit($diary->content, 100) }}</p>
                </a>
            @endforeach
        </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('diary.create') }}" class="btn btn-primary">➕ 新しい日記を書く</a>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container py-6 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">📈 あなたのステータス</h1>

    <p class="mb-4">📝 総投稿数：<strong>{{ $postCount }}</strong> 件</p>

    <div class="grid grid-cols-2 gap-4">
@foreach ($emotionStatuses as $emotion)
    <div class="p-4 mb-3 rounded shadow" style="background-color: {{ $emotion['color'] }}">
        <strong>{{ $emotion['label'] }}</strong><br>

        @if ($emotion['unlocked'])
            ✅ 解禁済み
            @if ($emotion['is_initial'])
                （初期感情）
            @elseif ($emotion['unlock_type'] === 'post_count')
                （投稿数によって解禁）
            @else
                （{{ $emotion['base'] }} の記録により解禁）
            @endif
        @else
            🔒
            @if ($emotion['unlock_type'] === 'post_count')
                あと {{ $emotion['remaining'] ?? 0 }} 回の投稿で解禁（{{ $emotion['current_count'] }} / {{ $emotion['required'] }}）
            @else
                あと {{ $emotion['remaining'] ?? 0 }} 回の {{ $emotion['base_emotion'] }} 投稿で解禁（{{ $emotion['current_count'] }} / {{ $emotion['required'] }}）
            @endif
        @endif
    </div>
@endforeach
    </div>
@endsection
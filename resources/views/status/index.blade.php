@extends('layouts.app')

@section('content')
<div class="container py-6 max-w-3xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">📈 あなたのステータス</h1>

    <p class="mb-4">📝 総投稿数：<strong>{{ $postCount }}</strong> 件</p>

    <div class="grid grid-cols-2 gap-4">
        @foreach ($emotionStatuses as $emotion)
            <div class="p-4 rounded shadow text-center" style="background-color: {{ $emotion['color'] }}; opacity: {{ $emotion['unlocked'] ? 1 : 0.3 }}">
                <p class="text-lg font-semibold">{{ $emotion['label'] }}</p>
                @if (!$emotion['unlocked'])
                    <p class="text-sm">あと {{ $emotion['required'] - $postCount }} 件で解禁</p>
                @else
                    <p class="text-sm text-green-700">解禁済み ✅</p>
                @endif
            </div>
        @endforeach
    </div>
</div>
@endsection

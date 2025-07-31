@extends('layouts.app')

@section('content')
    <div class="container py-10 max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6 border-b pb-2">📈 あなたのステータス</h1>

        <p class="mb-8 text-gray-700 text-lg">📝 総投稿数：<strong>{{ $postCount }}</strong> 件</p>

        {{-- 感情カテゴリ分類 --}}
        @php
            $grouped = collect($emotionStatuses)->groupBy(function ($e) {
                return $e->is_initial ? '基本感情' : 'アドバンス感情';
            });
        @endphp


        <div class="space-y-10">
            @foreach ($grouped as $category => $emotions)
                <div class="bg-white/70 backdrop-blur-md shadow-xl rounded-2xl p-6 border border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4 border-b pb-1">{{ $category }}</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($emotions as $emotion)
                            <div class="rounded-lg p-4 shadow-sm border border-white/50 bg-white/80 backdrop-blur-sm"
                                style="background-color: {{ $emotion->color }};">

                                <h3 class="text-lg font-bold mb-2">{{ $emotion->label }}</h3>

                                @if ($emotion->unlocked)
                                    <p class="text-sm text-green-800">
                                        ✅ 解禁済み
                                        @if ($emotion->is_initial)
                                            （初期感情）
                                        @elseif ($emotion->unlockType === 'post_count')
                                            （投稿数によって解禁）
                                        @else
                                            （{{ $emotion->base }} の記録により解禁）
                                        @endif
                                    </p>
                                @else
                                    <p class="text-sm text-gray-800">
                                        🔒
                                        @if ($emotion->unlockType === 'post_count')
                                            あと {{ $emotion->remaining ?? 0 }} 回の投稿で解禁
                                            （{{ $emotion->currentCount }} / {{ $emotion->threshold }}）
                                        @else
                                            あと {{ $emotion->remaining ?? 0 }} 回の
                                            {{ $emotion->baseEmotion }} 投稿で解禁
                                            （{{ $emotion->currentCount }} / {{ $emotion->threshold }}）
                                        @endif
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <div class="container max-w-4xl mx-auto space-y-10 py-6">

        {{-- グラフ①：感情カテゴリ別 投稿数 --}}
        <section class="bg-white/70 backdrop-blur-md shadow-xl rounded-2xl p-4 border border-gray-200">
            <h2 class="text-xl font-semibold mb-3">📊 感情カテゴリ別の投稿数</h2>
            <canvas id="chartCategory" class="w-full h-48"></canvas>
        </section>

        {{-- グラフ②：月別感情傾向 --}}
        <section class="bg-white/70 backdrop-blur-md shadow-xl rounded-2xl p-4 border border-gray-200">
            <h2 class="text-xl font-semibold mb-3">📅 月別の感情推移</h2>
            <x-chart.bar-chart :labels="$chartData->labels" :datasets="$chartData->datasets" :options="$chartData->options" id="monthly-status" />
        </section>


        {{-- グラフ③：直近感情スコア --}}
        <section class="bg-white/70 backdrop-blur-md shadow-xl rounded-2xl p-4 border border-gray-200">
            <h2 class="text-xl font-semibold mb-3">🧠 最近の感情スコア</h2>
            <canvas id="chartRadar" class="w-full h-48"></canvas>
        </section>

    </div>
@endsection
@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // グラフ①：カテゴリ別
            new Chart(document.getElementById('chartCategory'), {
                type: 'pie',
                data: {
                    labels: {!! json_encode(array_keys($baseEmotionChartData)) !!},
                    datasets: [{
                        data: {!! json_encode(array_values($baseEmotionChartData)) !!},
                        backgroundColor: ['#4CAF50', '#2196F3', '#F44336', '#9C27B0', '#FF9800',
                            '#795548'
                        ]
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // グラフ③：レーダー（直近投稿から抽出）
            new Chart(document.getElementById('chartRadar'), {
                type: 'radar',
                data: {
                    labels: ['嬉しい', '悲しい', '不安', 'イライラ', '驚いた', '嫌だった'],
                    datasets: [{
                        label: 'スコア',
                        data: {!! json_encode($recentEmotionScores) !!},
                        backgroundColor: 'rgba(76, 175, 80, 0.2)',
                        borderColor: '#4CAF50',
                        borderWidth: 2
                    }]
                }
            });
        });
    </script>
@endpush

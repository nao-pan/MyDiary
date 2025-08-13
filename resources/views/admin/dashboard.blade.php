@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">📊 アクセス解析ダッシュボード</h1>

    <form method="GET" class="mb-4">
        <select name="period" onchange="this.form.submit()">
            <option value="7" {{ $period==7 ? 'selected' : '' }}>過去7日</option>
            <option value="30" {{ $period==30 ? 'selected' : '' }}>過去30日</option>
            <option value="all" {{ $period=='all' ? 'selected' : '' }}>全期間</option>
        </select>
    </form>

    <canvas id="dailyChart" class="mb-8"></canvas>
    <canvas id="emotionChart"></canvas>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const dailyLabels = {!! json_encode($metrics->pluck('date')) !!};
    const postData = {!! json_encode($metrics->pluck('posts')) !!};
    const dauData = {!! json_encode($metrics->pluck('wau')) !!};

    new Chart(document.getElementById('dailyChart'), {
        type: 'bar',
        data: {
            labels: dailyLabels,
            datasets: [
                { label: '投稿数', data: postData, backgroundColor: 'rgba(75,192,192,0.6)' },
                { label: 'WAU', type: 'line', data: dauData, borderColor: 'rgba(153,102,255,1)', fill: false }
            ]
        }
    });

    const emotionLabels = {!! json_encode($emotionDistribution->keys()) !!};
    const emotionData = {!! json_encode($emotionDistribution->values()) !!};

    new Chart(document.getElementById('emotionChart'), {
        type: 'pie',
        data: {
            labels: emotionLabels,
            datasets: [{
                data: emotionData,
                backgroundColor: ['#FFD700','#87CEEB','#FF6347','#32CD32','#FF69B4','#FFA500']
            }]
        }
    });
</script>
@endsection
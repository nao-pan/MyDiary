@props([
    'pieChartData',
    'id' => 'chart-pie',
])

<canvas id="{{ $id }}"></canvas>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
            // グラフ①：カテゴリ別
            new Chart(document.getElementById("{{ $id }}"), {
                type: 'pie',
                data: @json($pieChartData->toArray()),
                options: @json($pieChartData->toArray()['options'])
                
        });
    });
</script>
@endpush

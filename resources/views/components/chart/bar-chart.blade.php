@props([
    'labels' => [],
    'datasets' => [],
    'id' => 'chart-bar',
    'options' => []
])

<canvas id="{{ $id }}"></canvas>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById("{{ $id }}");
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: @json($datasets)
                },
                options: @json($options)
            });
        });
    </script>
@endpush

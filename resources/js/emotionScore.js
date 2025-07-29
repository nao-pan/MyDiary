// {{-- JavaScriptによるラベルハイライト --}}
    function updateEmotionLabel(val) {
        const labels = ['0.2', '0.4', '0.6', '0.8', '1.0'];
        labels.forEach(score => {
            const el = document.getElementById('label-' + score);
            if (score === val) {
                el.classList.add('text-blue-600', 'font-bold');
            } else {
                el.classList.remove('text-blue-600', 'font-bold');
            }
        });
    }

    // 初期ハイライト
    document.addEventListener('DOMContentLoaded', function () {
        updateEmotionLabel(document.getElementById('emotion_score').value);
    });
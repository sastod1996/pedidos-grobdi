<script>
    function createChart(canvasId, labels, datasets, type, extraOptions = {}) {
        const ctx = $(canvasId).get(0).getContext('2d');
        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                ...extraOptions
            }
        });
    }
</script>
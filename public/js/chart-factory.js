function createChart(canvasId, labels, datasets, type, extraOptions = {}) {
    const canvas = document.querySelector(canvasId);

    if (!canvas) {
        console.error(`No se encontró el canvas con selector ${canvasId}`);
        return null;
    }

    const ctx = canvas.getContext("2d");

    return new Chart(ctx, {
        type: type,
        data: {
            labels: labels,
            datasets: datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            ...extraOptions,
        },
    });
}

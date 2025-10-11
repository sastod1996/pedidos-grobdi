function generateHslColors(
    items,
    alpha = 1,
    saturation = "70%",
    lightness = "55%",
    step = 37,
) {
    return items.map((_, i) => {
        const hue = (i * step) % 360;
        return `hsl(${hue} ${saturation} ${lightness} / ${alpha})`;
    });
}

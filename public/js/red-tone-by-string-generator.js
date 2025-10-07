function getColorByVisitadoraName(str) {
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = str.charCodeAt(i) + ((hash << 5) - hash);
    }

    // Color base derivado del hash
    let c = hash & 0x00ffffff;

    let r = (c >> 16) & 0xff;
    let g = (c >> 8) & 0xff;
    let b = c & 0xff;

    // 🔴 Mejorar la vivacidad del rojo
    r = Math.max(200, r); // Rojo más intenso como base
    g = Math.floor(g * 0.3); // Verde moderado para dar calidez
    b = Math.floor(b * 0.2); // Azul muy limitado para evitar tonos morados

    // Aplicar ajuste de saturación para mayor vivacidad
    const avg = (r + g + b) / 3;
    const saturation = 1.4; // Factor de saturación (aumenta la vivacidad)

    r = Math.min(255, Math.max(0, avg + saturation * (r - avg)));
    g = Math.min(255, Math.max(0, avg + saturation * (g - avg)));
    b = Math.min(255, Math.max(0, avg + saturation * (b - avg)));

    // Ajustar brillo para evitar colores apagados
    const brightness = 1.1; // Factor de brillo
    r = Math.min(255, r * brightness);
    g = Math.min(255, g * brightness);
    b = Math.min(255, b * brightness);

    // Clamp final para mantener colores vivos pero controlados
    r = Math.min(Math.max(r, 180), 255);
    g = Math.min(Math.max(g, 20), 100); // Verde suficiente para calidez pero no dominante
    b = Math.min(Math.max(b, 10), 70); // Azul mínimo para evitar tonos anaranjados puros

    return `rgba(${Math.round(r)}, ${Math.round(g)}, ${Math.round(b)}, 0.4)`;
}

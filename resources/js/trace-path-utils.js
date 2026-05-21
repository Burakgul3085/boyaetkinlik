/** Çizgi yolu yardımcıları — yumuşak eğriler için yoğun nokta üretimi */

export function line(p0, p1, steps = 10) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        pts.push([p0[0] + (p1[0] - p0[0]) * t, p0[1] + (p1[1] - p0[1]) * t]);
    }
    return pts;
}

export function quad(p0, p1, p2, steps = 22) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        const u = 1 - t;
        pts.push([
            u * u * p0[0] + 2 * u * t * p1[0] + t * t * p2[0],
            u * u * p0[1] + 2 * u * t * p1[1] + t * t * p2[1],
        ]);
    }
    return pts;
}

export function cubic(p0, p1, p2, p3, steps = 28) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        const u = 1 - t;
        pts.push([
            u * u * u * p0[0] + 3 * u * u * t * p1[0] + 3 * u * t * t * p2[0] + t * t * t * p3[0],
            u * u * u * p0[1] + 3 * u * u * t * p1[1] + 3 * u * t * t * p2[1] + t * t * t * p3[1],
        ]);
    }
    return pts;
}

export function arc(cx, cy, rx, ry, startAngle, endAngle, steps = 48) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const a = startAngle + ((endAngle - startAngle) * i) / steps;
        pts.push([cx + Math.cos(a) * rx, cy + Math.sin(a) * ry]);
    }
    return pts;
}

export function ellipse(cx, cy, rx, ry, steps = 56) {
    return arc(cx, cy, rx, ry, -Math.PI / 2, (3 * Math.PI) / 2, steps);
}

export function dot(cx, cy, r = 0.025) {
    return arc(cx, cy, r, r * 0.6, 0, Math.PI * 2, 16);
}

/** Sağa bakan yuvarlak bombe (B, D, P üst/alt) */
export function bumpRight(x, y0, y1, bulge = 0.42) {
    const ym = (y0 + y1) / 2;
    return cubic([x, y0], [x + bulge, y0], [x + bulge, ym], [x, ym], 18).concat(
        cubic([x, ym], [x + bulge, ym], [x + bulge, y1], [x, y1], 18).slice(1)
    );
}

/** Sola bakan C/ G yayı */
export function arcLeft(cx, cy, rx, ry) {
    return arc(cx, cy, rx, ry, Math.PI * 0.55, Math.PI * 1.45, 44);
}

export function letterMeta(display, level = 'Kolay') {
    return { name: display, display, level, segments: [] };
}

export function withSegments(display, level, segments) {
    return { name: display, display, level, segments };
}

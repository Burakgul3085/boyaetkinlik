/** Desen çizgi yolları (0–1 normalize koordinat, tek veya çok segment) */

function circlePts(cx, cy, r, steps = 36) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const a = (i / steps) * Math.PI * 2 - Math.PI / 2;
        pts.push([cx + Math.cos(a) * r, cy + Math.sin(a) * r]);
    }
    return pts;
}

function waveLine(y0, amp, waves, steps = 40) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        pts.push([0.08 + t * 0.84, y0 + Math.sin(t * Math.PI * waves) * amp]);
    }
    return pts;
}

function starPts(cx, cy, outer, inner, points = 5) {
    const pts = [];
    const n = points * 2;
    for (let i = 0; i < n; i++) {
        const a = (i / n) * Math.PI * 2 - Math.PI / 2;
        const r = i % 2 === 0 ? outer : inner;
        pts.push([cx + Math.cos(a) * r, cy + Math.sin(a) * r]);
    }
    pts.push(pts[0]);
    return pts;
}

export const SHAPE_PATTERNS = {
    ev: {
        name: 'Ev',
        level: 'Kolay',
        points: [
            [0, 1],
            [0, 0.35],
            [0.55, 0.35],
            [0.55, 1],
            [0.85, 1],
        ],
    },
    kelebek: {
        name: 'Kelebek',
        level: 'Orta',
        points: [
            [0.5, 0.08],
            [0.35, 0.35],
            [0.12, 0.55],
            [0.5, 0.72],
            [0.88, 0.55],
            [0.65, 0.35],
            [0.5, 0.08],
        ],
    },
    cicek: {
        name: 'Çiçek',
        level: 'Kolay',
        points: (function () {
            const pts = [];
            for (let i = 0; i < 8; i++) {
                const a = (i / 8) * Math.PI * 2 - Math.PI / 2;
                pts.push([0.5 + Math.cos(a) * 0.32, 0.55 + Math.sin(a) * 0.32]);
            }
            pts.push(pts[0]);
            return pts;
        })(),
    },
    yildiz: {
        name: 'Yıldız',
        level: 'Kolay',
        points: starPts(0.5, 0.5, 0.38, 0.16),
    },
    dalga: {
        name: 'Dalga',
        level: 'Orta',
        points: waveLine(0.5, 0.28, 3),
    },
    kalp: {
        name: 'Kalp',
        level: 'Kolay',
        points: [
            [0.5, 0.9],
            [0.18, 0.58],
            [0.22, 0.28],
            [0.5, 0.48],
            [0.78, 0.28],
            [0.82, 0.58],
            [0.5, 0.9],
        ],
    },
    agac: {
        name: 'Ağaç',
        level: 'Kolay',
        points: [
            [0.5, 0.92],
            [0.5, 0.52],
            [0.18, 0.52],
            [0.5, 0.1],
            [0.82, 0.52],
            [0.5, 0.52],
            [0.5, 0.92],
        ],
    },
    balon: {
        name: 'Balon',
        level: 'Kolay',
        points: [
            ...circlePts(0.5, 0.38, 0.28, 28).slice(0, -1),
            [0.5, 0.66],
            [0.5, 0.88],
        ],
    },
    balik: {
        name: 'Balık',
        level: 'Orta',
        points: [
            [0.12, 0.5],
            [0.35, 0.32],
            [0.62, 0.3],
            [0.88, 0.5],
            [0.62, 0.7],
            [0.35, 0.68],
            [0.12, 0.5],
            [0.88, 0.5],
            [0.98, 0.38],
            [0.98, 0.62],
            [0.88, 0.5],
        ],
    },
    bulut: {
        name: 'Bulut',
        level: 'Kolay',
        points: waveLine(0.52, 0.14, 4, 36),
    },
    gemi: {
        name: 'Gemi',
        level: 'Orta',
        points: [
            [0.15, 0.62],
            [0.35, 0.78],
            [0.75, 0.78],
            [0.9, 0.62],
            [0.5, 0.62],
            [0.5, 0.22],
            [0.72, 0.22],
            [0.5, 0.42],
            [0.28, 0.22],
            [0.5, 0.22],
            [0.5, 0.62],
            [0.15, 0.62],
        ],
    },
    gunes: {
        name: 'Güneş',
        level: 'Kolay',
        points: (function () {
            const pts = [];
            const rays = 8;
            for (let i = 0; i < rays; i++) {
                const a = (i / rays) * Math.PI * 2 - Math.PI / 2;
                pts.push([0.5 + Math.cos(a) * 0.2, 0.5 + Math.sin(a) * 0.2]);
                pts.push([0.5 + Math.cos(a) * 0.38, 0.5 + Math.sin(a) * 0.38]);
                pts.push([0.5 + Math.cos(a) * 0.2, 0.5 + Math.sin(a) * 0.2]);
            }
            pts.push(pts[0]);
            return pts;
        })(),
    },
    ay: {
        name: 'Ay',
        level: 'Kolay',
        points: (function () {
            const pts = [];
            for (let i = 0; i <= 28; i++) {
                const a = -Math.PI / 2 + (i / 28) * Math.PI * 1.4;
                pts.push([0.42 + Math.cos(a) * 0.32, 0.5 + Math.sin(a) * 0.32]);
            }
            for (let i = 28; i >= 0; i--) {
                const a = -Math.PI / 2 + (i / 28) * Math.PI * 1.4;
                pts.push([0.58 + Math.cos(a) * 0.22, 0.5 + Math.sin(a) * 0.22]);
            }
            pts.push(pts[0]);
            return pts;
        })(),
    },
    araba: {
        name: 'Araba',
        level: 'Orta',
        points: [
            [0.12, 0.62],
            [0.22, 0.42],
            [0.42, 0.38],
            [0.72, 0.38],
            [0.88, 0.48],
            [0.92, 0.62],
            [0.78, 0.62],
            [0.78, 0.72],
            [0.68, 0.72],
            [0.68, 0.62],
            [0.32, 0.62],
            [0.32, 0.72],
            [0.22, 0.72],
            [0.22, 0.62],
            [0.12, 0.62],
        ],
    },
    kus: {
        name: 'Kuş',
        level: 'Kolay',
        points: [
            [0.5, 0.48],
            [0.2, 0.35],
            [0.08, 0.52],
            [0.28, 0.58],
            [0.5, 0.72],
            [0.72, 0.58],
            [0.92, 0.52],
            [0.8, 0.35],
            [0.5, 0.48],
            [0.5, 0.22],
        ],
    },
    mantar: {
        name: 'Mantar',
        level: 'Kolay',
        points: [
            [0.22, 0.55],
            [0.28, 0.32],
            [0.5, 0.18],
            [0.72, 0.32],
            [0.78, 0.55],
            [0.58, 0.55],
            [0.58, 0.88],
            [0.42, 0.88],
            [0.42, 0.55],
            [0.22, 0.55],
        ],
    },
    kar: {
        name: 'Kar Tanesi',
        level: 'Orta',
        points: (function () {
            const pts = [[0.5, 0.5]];
            for (let i = 0; i < 6; i++) {
                const a = (i / 6) * Math.PI * 2 - Math.PI / 2;
                pts.push([0.5 + Math.cos(a) * 0.38, 0.5 + Math.sin(a) * 0.38]);
                pts.push([0.5 + Math.cos(a) * 0.18, 0.5 + Math.sin(a) * 0.18]);
                pts.push([0.5, 0.5]);
            }
            return pts;
        })(),
    },
    ucgen: {
        name: 'Üçgen',
        level: 'Kolay',
        points: [
            [0.5, 0.12],
            [0.14, 0.88],
            [0.86, 0.88],
            [0.5, 0.12],
        ],
    },
    kare: {
        name: 'Kare',
        level: 'Kolay',
        points: [
            [0.18, 0.18],
            [0.82, 0.18],
            [0.82, 0.82],
            [0.18, 0.82],
            [0.18, 0.18],
        ],
    },
    daire: {
        name: 'Daire',
        level: 'Kolay',
        points: circlePts(0.5, 0.5, 0.36),
    },
    roket: {
        name: 'Roket',
        level: 'Orta',
        points: [
            [0.5, 0.08],
            [0.68, 0.55],
            [0.58, 0.55],
            [0.58, 0.88],
            [0.42, 0.88],
            [0.42, 0.55],
            [0.32, 0.55],
            [0.5, 0.08],
        ],
    },
    yaprak: {
        name: 'Yaprak',
        level: 'Kolay',
        points: [
            [0.5, 0.88],
            [0.22, 0.55],
            [0.28, 0.28],
            [0.5, 0.12],
            [0.72, 0.28],
            [0.78, 0.55],
            [0.5, 0.88],
        ],
    },
    elma: {
        name: 'Elma',
        level: 'Kolay',
        points: [
            ...circlePts(0.5, 0.55, 0.3, 24).slice(0, -1),
            [0.5, 0.25],
            [0.5, 0.12],
        ],
    },
    zikzak: {
        name: 'Zikzak',
        level: 'Orta',
        points: (function () {
            const pts = [];
            const peaks = 6;
            for (let i = 0; i <= peaks; i++) {
                pts.push([0.1 + (i / peaks) * 0.8, i % 2 === 0 ? 0.28 : 0.72]);
            }
            return pts;
        })(),
    },
    spiral: {
        name: 'Spiral',
        level: 'Zor',
        points: (function () {
            const pts = [];
            for (let i = 0; i <= 64; i++) {
                const t = i / 64;
                const a = t * Math.PI * 5 - Math.PI / 2;
                const r = 0.06 + t * 0.34;
                pts.push([0.5 + Math.cos(a) * r, 0.5 + Math.sin(a) * r]);
            }
            return pts;
        })(),
    },
};

/** Desen çizgi yolları (tek segment veya çok segment) */
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
        points: (function () {
            const pts = [];
            const outer = 0.38;
            const inner = 0.16;
            for (let i = 0; i < 10; i++) {
                const a = (i / 10) * Math.PI * 2 - Math.PI / 2;
                const r = i % 2 === 0 ? outer : inner;
                pts.push([0.5 + Math.cos(a) * r, 0.5 + Math.sin(a) * r]);
            }
            pts.push(pts[0]);
            return pts;
        })(),
    },
    dalga: {
        name: 'Dalga',
        level: 'Orta',
        points: (function () {
            const pts = [];
            for (let i = 0; i <= 40; i++) {
                const t = i / 40;
                pts.push([0.08 + t * 0.84, 0.5 + Math.sin(t * Math.PI * 3) * 0.28]);
            }
            return pts;
        })(),
    },
};

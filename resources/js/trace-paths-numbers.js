/** 0–9 sayı çizgi yolları */
export const NUMBER_PATTERNS = {
    '0': {
        name: '0',
        display: '0',
        level: 'Kolay',
        segments: [
            (function () {
                const pts = [];
                for (let i = 0; i <= 32; i++) {
                    const a = (i / 32) * Math.PI * 2 - Math.PI / 2;
                    pts.push([0.5 + Math.cos(a) * 0.28, 0.5 + Math.sin(a) * 0.38]);
                }
                return pts;
            })(),
        ],
    },
    '1': {
        name: '1',
        display: '1',
        level: 'Kolay',
        segments: [
            [[0.42, 0.22], [0.5, 0.08], [0.5, 0.92]],
            [[0.32, 0.92], [0.68, 0.92]],
        ],
    },
    '2': {
        name: '2',
        display: '2',
        level: 'Kolay',
        segments: [[[0.22, 0.28], [0.38, 0.08], [0.68, 0.12], [0.78, 0.35], [0.58, 0.52], [0.22, 0.92], [0.78, 0.92]]],
    },
    '3': {
        name: '3',
        display: '3',
        level: 'Orta',
        segments: [
            [[0.28, 0.18], [0.55, 0.08], [0.72, 0.28], [0.55, 0.48], [0.72, 0.68], [0.55, 0.92], [0.28, 0.82]],
            [[0.28, 0.48], [0.58, 0.48]],
        ],
    },
    '4': {
        name: '4',
        display: '4',
        level: 'Kolay',
        segments: [
            [[0.58, 0.08], [0.28, 0.65], [0.78, 0.65]],
            [[0.58, 0.08], [0.58, 0.92]],
        ],
    },
    '5': {
        name: '5',
        display: '5',
        level: 'Orta',
        segments: [
            [[0.72, 0.08], [0.28, 0.08], [0.28, 0.45], [0.62, 0.45], [0.75, 0.62], [0.62, 0.92], [0.28, 0.88]],
        ],
    },
    '6': {
        name: '6',
        display: '6',
        level: 'Orta',
        segments: [
            [[0.62, 0.18], [0.42, 0.08], [0.28, 0.35], [0.28, 0.65], [0.48, 0.92], [0.68, 0.82], [0.72, 0.58], [0.52, 0.48], [0.32, 0.52]],
        ],
    },
    '7': {
        name: '7',
        display: '7',
        level: 'Kolay',
        segments: [
            [[0.22, 0.08], [0.78, 0.08], [0.38, 0.92]],
            [[0.32, 0.92], [0.48, 0.92]],
        ],
    },
    '8': {
        name: '8',
        display: '8',
        level: 'Zor',
        segments: [
            (function () {
                const pts = [];
                for (let i = 0; i <= 20; i++) {
                    const a = (i / 20) * Math.PI * 2 - Math.PI / 2;
                    pts.push([0.5 + Math.cos(a) * 0.22, 0.32 + Math.sin(a) * 0.2]);
                }
                return pts;
            })(),
            (function () {
                const pts = [];
                for (let i = 0; i <= 20; i++) {
                    const a = (i / 20) * Math.PI * 2 - Math.PI / 2;
                    pts.push([0.5 + Math.cos(a) * 0.26, 0.62 + Math.sin(a) * 0.26]);
                }
                return pts;
            })(),
        ],
    },
    '9': {
        name: '9',
        display: '9',
        level: 'Orta',
        segments: [
            (function () {
                const pts = [];
                for (let i = 0; i <= 24; i++) {
                    const a = (i / 24) * Math.PI * 2 - Math.PI / 2;
                    pts.push([0.5 + Math.cos(a) * 0.28, 0.38 + Math.sin(a) * 0.24]);
                }
                return pts;
            })(),
            [[0.5, 0.58], [0.5, 0.92]],
        ],
    },
};

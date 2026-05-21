/**
 * Gerçek font konturundan harf/rakam çizgi yolları (Nunito Sans)
 * Koordinatlar doğrudan tuval (canvas) uzayında — y ekseni aşağı
 */
import { parse as parseFont } from 'opentype.js';

export const CANVAS_W = 560;
export const CANVAS_H = 360;
const FONT_LATIN_URL = '/fonts/nunito-sans-latin.woff';
const FONT_LATIN_EXT_URL = '/fonts/nunito-sans-latin-ext.woff';
const FONT_SIZE = 220;
const CANVAS_PAD = 48;

const LETTER_LEVELS = {
    Kolay: 'A,C,E,F,I,L,O,T,V',
    Orta: 'B,D,H,K,M,N,P,R,U,Y,Z,Ç,Ö,Ü',
    Zor: 'G,Ğ,J,S,Ş',
};

const NUMBER_LEVELS = {
    Kolay: '0,1,4,7',
    Orta: '2,3,5,9',
    Zor: '6,8',
};

let fontLatin = null;
let fontLatinExt = null;

function levelForChar(char, map) {
    for (const [level, chars] of Object.entries(map)) {
        if (chars.split(',').includes(char)) return level;
    }
    return 'Kolay';
}

async function loadWoff(url) {
    const res = await fetch(url);
    if (!res.ok) throw new Error('Font yüklenemedi: ' + url);
    return parseFont(await res.arrayBuffer());
}

function hasRealGlyph(font, char) {
    return font.charToGlyph(char).index !== 0;
}

function fontForChar(char) {
    if (fontLatin && hasRealGlyph(fontLatin, char)) return fontLatin;
    if (fontLatinExt && hasRealGlyph(fontLatinExt, char)) return fontLatinExt;
    return fontLatin || fontLatinExt;
}

export async function loadTraceFont() {
    if (fontLatin && fontLatinExt) return { fontLatin, fontLatinExt };
    const [latin, ext] = await Promise.all([loadWoff(FONT_LATIN_URL), loadWoff(FONT_LATIN_EXT_URL)]);
    fontLatin = latin;
    fontLatinExt = ext;
    return { fontLatin, fontLatinExt };
}

function sampleCubic(x0, y0, x1, y1, x2, y2, x3, y3, steps = 14) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        const u = 1 - t;
        pts.push([
            u * u * u * x0 + 3 * u * u * t * x1 + 3 * u * t * t * x2 + t * t * t * x3,
            u * u * u * y0 + 3 * u * u * t * y1 + 3 * u * t * t * y2 + t * t * t * y3,
        ]);
    }
    return pts;
}

function sampleQuad(x0, y0, x1, y1, x2, y2, steps = 12) {
    const pts = [];
    for (let i = 0; i <= steps; i++) {
        const t = i / steps;
        const u = 1 - t;
        pts.push([u * u * x0 + 2 * u * t * x1 + t * t * x2, u * u * y0 + 2 * u * t * y1 + t * t * y2]);
    }
    return pts;
}

/** Font yukarı → tuval y aşağı: canvasY = 2*baseline - fontY */
function pathToCanvasSegments(path, baselineY) {
    const toCanvas = (x, y) => [x, baselineY * 2 - y];
    const segments = [];
    let current = [];
    let cx = 0;
    let cy = 0;
    let sx = 0;
    let sy = 0;

    const pushPt = (x, y) => {
        const p = toCanvas(x, y);
        if (current.length === 0) {
            current.push(p);
            return;
        }
        const last = current[current.length - 1];
        if (Math.hypot(last[0] - p[0], last[1] - p[1]) > 0.5) current.push(p);
    };

    const flush = () => {
        if (current.length > 1) segments.push(current);
        current = [];
    };

    path.commands.forEach((cmd) => {
        switch (cmd.type) {
            case 'M':
                flush();
                cx = cmd.x;
                cy = cmd.y;
                sx = cx;
                sy = cy;
                current = [];
                pushPt(cx, cy);
                break;
            case 'L':
                cx = cmd.x;
                cy = cmd.y;
                pushPt(cx, cy);
                break;
            case 'C': {
                const pts = sampleCubic(cx, cy, cmd.x1, cmd.y1, cmd.x2, cmd.y2, cmd.x, cmd.y);
                pts.slice(1).forEach(([x, y]) => pushPt(x, y));
                cx = cmd.x;
                cy = cmd.y;
                break;
            }
            case 'Q': {
                const pts = sampleQuad(cx, cy, cmd.x1, cmd.y1, cmd.x, cmd.y);
                pts.slice(1).forEach(([x, y]) => pushPt(x, y));
                cx = cmd.x;
                cy = cmd.y;
                break;
            }
            case 'Z':
                pushPt(sx, sy);
                flush();
                break;
            default:
                break;
        }
    });
    flush();
    return segments;
}

export function buildCharPattern(char, level = 'Kolay') {
    const font = fontForChar(char);
    if (!font) throw new Error('Font hazır değil');

    const probe = font.getPath(char, 0, 0, FONT_SIZE);
    const bb = probe.getBoundingBox();
    const glyphW = bb.x2 - bb.x1;
    const glyphH = bb.y2 - bb.y1;

    const startX = (CANVAS_W - glyphW) / 2 - bb.x1;
    const baselineY = CANVAS_H - CANVAS_PAD;

    const path = font.getPath(char, startX, baselineY, FONT_SIZE);
    const segments = pathToCanvasSegments(path, baselineY);

    return {
        name: char,
        display: char,
        level,
        segments,
        canvasSpace: true,
    };
}

export async function buildLetterPatterns() {
    await loadTraceFont();
    const chars =
        'A,B,C,Ç,D,E,F,G,Ğ,H,I,İ,J,K,L,M,N,O,Ö,P,R,S,Ş,T,U,Ü,V,Y,Z'.split(',');
    const patterns = {};
    chars.forEach((ch) => {
        patterns[ch] = buildCharPattern(ch, levelForChar(ch, LETTER_LEVELS));
    });
    return patterns;
}

export async function buildNumberPatterns() {
    await loadTraceFont();
    const patterns = {};
    for (let d = 0; d <= 9; d++) {
        const ch = String(d);
        patterns[ch] = buildCharPattern(ch, levelForChar(ch, NUMBER_LEVELS));
    }
    return patterns;
}

/**
 * Gerçek font konturundan harf/rakam çizgi yolları (Nunito Sans)
 * latin + latin-ext: rakamlar ve Z latin'te, İ/Ğ latin-ext'te
 */
import { parse as parseFont } from 'opentype.js';

const FONT_LATIN_URL = '/fonts/nunito-sans-latin.woff';
const FONT_LATIN_EXT_URL = '/fonts/nunito-sans-latin-ext.woff';
const FONT_SIZE = 220;

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
    const g = font.charToGlyph(char);
    return g.index !== 0;
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

function normPoint(x, y, bb, pad = 0.1) {
    const fw = bb.x2 - bb.x1 || 1;
    const fh = bb.y2 - bb.y1 || 1;
    const s = Math.min((1 - pad * 2) / fw, (1 - pad * 2) / fh);
    const ox = pad + (1 - pad * 2 - fw * s) / 2;
    const oy = pad + (1 - pad * 2 - fh * s) / 2;
    return [ox + (x - bb.x1) * s, oy + (bb.y2 - y) * s];
}

function pathToSegments(path) {
    const bb = path.getBoundingBox();
    const segments = [];
    let current = [];
    let cx = 0;
    let cy = 0;
    let sx = 0;
    let sy = 0;

    const pushPt = (x, y) => {
        const p = normPoint(x, y, bb);
        if (current.length === 0) current.push(p);
        else {
            const last = current[current.length - 1];
            if (Math.hypot(last[0] - p[0], last[1] - p[1]) > 0.004) current.push(p);
        }
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
    const glyph = font.charToGlyph(char);
    if (glyph.index === 0) {
        console.warn('[trace-font] Glyph bulunamadı:', char);
    }
    const path = font.getPath(char, 0, 0, FONT_SIZE);
    const segments = pathToSegments(path);
    return {
        name: char,
        display: char,
        level,
        segments,
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

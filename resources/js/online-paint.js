/**
 * Online Boya — çizgi üstte, boyama altta; tıklamalar paint-hit-layer üzerinden.
 */
const MAX_UNDO = 40;
let paintStudioStarted = false;

const COLOR_THEMES = {
    pastel: ['#fecaca', '#fed7aa', '#fef08a', '#bbf7d0', '#a5f3fc', '#ddd6fe', '#fbcfe8', '#f5f5f4'],
    vivid: ['#dc2626', '#ea580c', '#ca8a04', '#16a34a', '#0891b2', '#2563eb', '#7c3aed', '#db2777'],
    nature: ['#166534', '#3f6212', '#854d0e', '#0f766e', '#1d4ed8', '#78716c', '#a16207', '#4ade80'],
    skin: ['#fef3c7', '#fde68a', '#fcd34d', '#fbbf24', '#f59e0b', '#d97706', '#b45309', '#92400e'],
};

const BRUSH_PRESETS = {
    detail: { tool: 'pencil', size: 6, opacity: 100, softness: 0 },
    normal: { tool: 'brush', size: 18, opacity: 100, softness: 35 },
    wide: { tool: 'marker', size: 40, opacity: 92, softness: 0 },
    spraySoft: { tool: 'spray', size: 28, opacity: 55, softness: 40 },
};

function bootOnlinePaint() {
    if (paintStudioStarted) return;
    const config = window.__ONLINE_PAINT__;
    if (!config?.lineArtUrl) return;
    paintStudioStarted = true;

    const paintCanvas = document.getElementById('paint-canvas');
    const lineCanvas = document.getElementById('line-canvas');
    const hitLayer = document.getElementById('paint-hit-layer');
    const stage = document.getElementById('canvas-stage');
    const scaler = document.getElementById('canvas-scaler');
    const orbitZone = document.getElementById('canvas-orbit-zone');
    const orbitRing = document.getElementById('paint-orbit-ring');
    const orbitGlow = document.getElementById('paint-orbit-glow');
    const wrap = document.getElementById('canvas-wrap');
    const loader = document.getElementById('paint-loader');
    const errBox = document.getElementById('paint-error');

    if (!paintCanvas || !lineCanvas || !hitLayer || !stage || !wrap) {
        return;
    }

    let paintCtx = paintCanvas.getContext('2d', { willReadFrequently: true });
    let lineCtx = lineCanvas.getContext('2d');

    const state = {
        tool: 'brush',
        color: '#ef4444',
        size: 18,
        opacity: 100,
        softness: 35,
        fillTolerance: 40,
        viewZoom: 1,
        viewRotate: 0,
        baseDisplayW: 0,
        baseDisplayH: 0,
        drawing: false,
        pointerId: null,
        lastX: 0,
        lastY: 0,
        undoStack: [],
        redoStack: [],
        recentColors: [],
        naturalW: 0,
        naturalH: 0,
        scale: 1,
        linePixels: null,
        ready: false,
    };

    const toolButtons = document.querySelectorAll('[data-paint-tool]');
    const colorInput = document.getElementById('paint-color-custom');
    const colorPreviewSwatch = document.getElementById('paint-color-preview-swatch');
    const colorPreviewHex = document.getElementById('paint-color-preview-hex');
    const recentColorsEl = document.getElementById('paint-recent-colors');
    const themeStripEl = document.getElementById('paint-theme-strip');
    const randomColorBtn = document.getElementById('paint-random-color');
    const fillToleranceInput = document.getElementById('paint-fill-tolerance');
    const fillToleranceLabel = document.getElementById('paint-fill-tolerance-label');
    const sizeInput = document.getElementById('paint-size');
    const sizeLabel = document.getElementById('paint-size-label');
    const opacityInput = document.getElementById('paint-opacity');
    const opacityLabel = document.getElementById('paint-opacity-label');
    const softnessInput = document.getElementById('paint-softness');
    const softnessLabel = document.getElementById('paint-softness-label');
    const swatches = document.querySelectorAll('[data-paint-color]');
    const undoBtn = document.getElementById('paint-undo');
    const redoBtn = document.getElementById('paint-redo');
    const clearBtn = document.getElementById('paint-clear');
    const zoomFitBtn = document.getElementById('paint-zoom-fit');
    const zoomInBtn = document.getElementById('paint-zoom-in');
    const zoomOutBtn = document.getElementById('paint-zoom-out');
    const zoomLabel = document.getElementById('paint-zoom-label');
    const rotateLeftBtn = document.getElementById('paint-rotate-left');
    const rotateRightBtn = document.getElementById('paint-rotate-right');
    const rotateResetBtn = document.getElementById('paint-rotate-reset');
    const rotateLabel = document.getElementById('paint-rotate-label');
    const orbitToggle = document.getElementById('paint-orbit-toggle');
    const glowToggle = document.getElementById('paint-glow-toggle');
    const fullscreenBtn = document.getElementById('paint-fullscreen');
    const downloadBtns = document.querySelectorAll('[data-paint-download]');
    const printBtn = document.getElementById('paint-print');
    const emailForm = document.getElementById('paint-email-form');

    function showError(msg) {
        if (loader) {
            loader.classList.add('hidden');
            loader.style.pointerEvents = 'none';
        }
        if (errBox) {
            errBox.textContent = msg;
            errBox.classList.remove('hidden');
        }
    }

    function setupCanvasContexts() {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        paintCanvas.width = Math.floor(state.naturalW * dpr);
        paintCanvas.height = Math.floor(state.naturalH * dpr);
        lineCanvas.width = Math.floor(state.naturalW * dpr);
        lineCanvas.height = Math.floor(state.naturalH * dpr);
        paintCtx = paintCanvas.getContext('2d', { willReadFrequently: true });
        lineCtx = lineCanvas.getContext('2d');
        paintCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        lineCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function isCanvasFullscreen() {
        const fs = document.fullscreenElement;
        return fs === wrap || (fs && wrap && fs.contains(wrap));
    }

    /** Ürün önizlemesi gibi: önce genişliği doldur */
    function fitToView() {
        if (!state.naturalW || !state.naturalH) return;

        const pad = 12;
        let maxW;
        let maxH;
        if (isCanvasFullscreen()) {
            maxW = Math.max(window.innerWidth - 40, 320);
            maxH = Math.max(window.innerHeight - 56, 320);
        } else {
            maxW = Math.max(wrap.clientWidth - pad, 320);
            maxH = Math.max(wrap.clientHeight - pad, 400);
        }

        let scale = maxW / state.naturalW;
        let displayH = state.naturalH * scale;

        if (displayH > maxH * 1.15) {
            scale = maxH / state.naturalH;
        }

        state.scale = scale;
        const displayW = Math.max(1, Math.round(state.naturalW * scale));
        displayH = Math.max(1, Math.round(state.naturalH * scale));

        state.baseDisplayW = displayW;
        state.baseDisplayH = displayH;
        stage.style.width = `${displayW}px`;
        stage.style.height = `${displayH}px`;
        stage.style.aspectRatio = `${state.naturalW} / ${state.naturalH}`;
        applyViewTransform();
    }

    function applyViewTransform() {
        const z = state.viewZoom;
        const r = state.viewRotate;
        if (scaler) {
            scaler.style.transform = `scale(${z}) rotate(${r}deg)`;
        }
        if (zoomLabel) {
            zoomLabel.textContent = `${Math.round(z * 100)}%`;
        }
        if (rotateLabel) {
            rotateLabel.textContent = `${r}°`;
        }
    }

    function unrotateNormalized(lx, ly, degrees) {
        const steps = ((((degrees % 360) + 360) % 360) / 90) | 0;
        let x = lx;
        let y = ly;
        for (let i = 0; i < steps; i++) {
            const nx = y;
            const ny = -x;
            x = nx;
            y = ny;
        }
        return { x, y };
    }

    function resizeCanvases(w, h) {
        state.naturalW = w;
        state.naturalH = h;
        setupCanvasContexts();
        paintCtx.fillStyle = '#ffffff';
        paintCtx.fillRect(0, 0, w, h);
        fitToView();
    }

    function cacheLinePixels() {
        const w = lineCanvas.width;
        const h = lineCanvas.height;
        if (!w || !h) {
            state.linePixels = null;
            return;
        }
        state.linePixels = lineCtx.getImageData(0, 0, w, h).data;
    }

    /** JPG/PNG beyaz zeminini şeffaf yap — boyama katmanı görünsün */
    function applyLineArtTransparency() {
        const w = lineCanvas.width;
        const h = lineCanvas.height;
        const imageData = lineCtx.getImageData(0, 0, w, h);
        const d = imageData.data;

        for (let i = 0; i < d.length; i += 4) {
            const r = d[i];
            const g = d[i + 1];
            const b = d[i + 2];
            const lum = r * 0.299 + g * 0.587 + b * 0.114;

            if (lum >= 245) {
                d[i + 3] = 0;
            } else if (lum <= 95) {
                d[i] = 0;
                d[i + 1] = 0;
                d[i + 2] = 0;
                d[i + 3] = 255;
            } else {
                d[i + 3] = Math.max(0, Math.min(255, Math.round((245 - lum) * 4.5)));
            }
        }

        lineCtx.putImageData(imageData, 0, 0);
        cacheLinePixels();
    }

    function isLineBarrier(px, py) {
        const data = state.linePixels;
        if (!data) return false;
        const w = lineCanvas.width;
        const h = lineCanvas.height;
        if (px < 0 || py < 0 || px >= w || py >= h) return true;
        const idx = (py * w + px) * 4;
        const a = data[idx + 3];
        if (a < 64) return false;
        const lum = data[idx] * 0.299 + data[idx + 1] * 0.587 + data[idx + 2] * 0.114;
        return lum < 210;
    }

    function canvasPoint(clientX, clientY) {
        const rect = hitLayer.getBoundingClientRect();
        if (!rect.width || !rect.height) {
            return { x: 0, y: 0 };
        }
        let lx = (clientX - rect.left) / rect.width - 0.5;
        let ly = (clientY - rect.top) / rect.height - 0.5;
        const unrot = unrotateNormalized(lx, ly, state.viewRotate);
        const x = (unrot.x + 0.5) * state.naturalW;
        const y = (unrot.y + 0.5) * state.naturalH;
        return {
            x: Math.max(0, Math.min(state.naturalW, x)),
            y: Math.max(0, Math.min(state.naturalH, y)),
        };
    }

    function syncOrbitEffects() {
        if (orbitRing) {
            orbitRing.classList.toggle('online-paint-orbit-ring--on', !!orbitToggle?.checked);
        }
        if (orbitGlow) {
            orbitGlow.classList.toggle('online-paint-orbit-glow--on', !!glowToggle?.checked);
        }
    }

    function saveUndo() {
        try {
            const snap = paintCtx.getImageData(0, 0, paintCanvas.width, paintCanvas.height);
            state.undoStack.push(snap);
            if (state.undoStack.length > MAX_UNDO) state.undoStack.shift();
            state.redoStack = [];
        } catch {
            /* */
        }
    }

    function restoreSnapshot(snap) {
        if (snap) paintCtx.putImageData(snap, 0, 0);
    }

    function undo() {
        if (state.undoStack.length === 0) return;
        state.redoStack.push(paintCtx.getImageData(0, 0, paintCanvas.width, paintCanvas.height));
        restoreSnapshot(state.undoStack.pop());
    }

    function redo() {
        if (state.redoStack.length === 0) return;
        state.undoStack.push(paintCtx.getImageData(0, 0, paintCanvas.width, paintCanvas.height));
        restoreSnapshot(state.redoStack.pop());
    }

    function setTool(tool) {
        state.tool = tool;
        toolButtons.forEach((btn) => {
            btn.classList.toggle('online-paint-tool--active', btn.dataset.paintTool === tool);
        });
        const cursor =
            tool === 'fill' ? 'cell' : tool === 'eraser' ? 'grab' : tool === 'picker' ? 'copy' : 'crosshair';
        hitLayer.style.cursor = cursor;
    }

    function applyBrushPreset(presetKey) {
        const preset = BRUSH_PRESETS[presetKey];
        if (!preset) return;
        setTool(preset.tool);
        state.size = preset.size;
        state.opacity = preset.opacity;
        state.softness = preset.softness;
        if (sizeInput) sizeInput.value = String(state.size);
        if (sizeLabel) sizeLabel.textContent = String(state.size);
        if (opacityInput) opacityInput.value = String(state.opacity);
        if (opacityLabel) opacityLabel.textContent = `${state.opacity}%`;
        if (softnessInput) softnessInput.value = String(state.softness);
        if (softnessLabel) softnessLabel.textContent = `${state.softness}%`;
        document.querySelectorAll('[data-paint-brush-preset]').forEach((btn) => {
            btn.classList.toggle('online-paint-chip--active', btn.dataset.paintBrushPreset === presetKey);
        });
    }

    function showThemeStrip(themeKey) {
        const colors = COLOR_THEMES[themeKey];
        if (!themeStripEl || !colors) return;
        themeStripEl.innerHTML = '';
        themeStripEl.classList.remove('hidden');
        colors.forEach((hex) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'online-paint-swatch';
            btn.dataset.paintColor = hex;
            btn.style.backgroundColor = hex;
            btn.title = hex;
            btn.addEventListener('click', () => setColor(hex));
            themeStripEl.appendChild(btn);
        });
        setColor(colors[0]);
        document.querySelectorAll('[data-paint-theme]').forEach((btn) => {
            btn.classList.toggle('online-paint-chip--active', btn.dataset.paintTheme === themeKey);
        });
    }

    function randomColor() {
        const hex = `#${Math.floor(Math.random() * 0xffffff)
            .toString(16)
            .padStart(6, '0')}`;
        setColor(hex);
    }

    function hexToRgba(hex) {
        const h = hex.replace('#', '');
        const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
        const n = parseInt(full, 16);
        return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255, a: 255 };
    }

    function rgbToHex(r, g, b) {
        const h = (v) => v.toString(16).padStart(2, '0');
        return `#${h(r)}${h(g)}${h(b)}`.toUpperCase();
    }

    function strokeColor() {
        if (state.tool === 'eraser') {
            return 'rgba(0,0,0,1)';
        }
        const c = hexToRgba(state.color);
        const a = state.opacity / 100;
        return `rgba(${c.r},${c.g},${c.b},${a})`;
    }

    function fillRgb() {
        const c = hexToRgba(state.color);
        const a = Math.round((state.opacity / 100) * 255);
        return { ...c, a };
    }

    function updateColorPreview() {
        if (colorPreviewSwatch) {
            colorPreviewSwatch.style.backgroundColor = state.color;
        }
        if (colorPreviewHex) {
            colorPreviewHex.textContent = state.color.toUpperCase();
        }
    }

    function renderRecentColors() {
        if (!recentColorsEl) return;
        recentColorsEl.innerHTML = '';
        if (state.recentColors.length === 0) {
            const empty = document.createElement('span');
            empty.className = 'online-paint-recent__empty';
            empty.textContent = 'Henüz yok';
            recentColorsEl.appendChild(empty);
            return;
        }
        state.recentColors.forEach((hex) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'online-paint-swatch';
            btn.dataset.paintColor = hex;
            btn.style.backgroundColor = hex;
            btn.title = hex;
            btn.addEventListener('click', () => setColor(hex));
            recentColorsEl.appendChild(btn);
        });
    }

    function pushRecentColor(hex) {
        const normalized = hex.toLowerCase();
        state.recentColors = state.recentColors.filter((c) => c.toLowerCase() !== normalized);
        state.recentColors.unshift(hex);
        if (state.recentColors.length > 8) {
            state.recentColors.pop();
        }
        renderRecentColors();
    }

    function setColor(hex) {
        state.color = hex;
        if (colorInput) colorInput.value = hex;
        swatches.forEach((s) => {
            s.classList.toggle('online-paint-swatch--active', s.dataset.paintColor?.toLowerCase() === hex.toLowerCase());
        });
        updateColorPreview();
        pushRecentColor(hex);
    }

    function configureBrush() {
        paintCtx.shadowBlur = 0;
        if (state.tool === 'eraser') {
            paintCtx.globalCompositeOperation = 'destination-out';
            paintCtx.lineCap = 'round';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = state.size;
            paintCtx.strokeStyle = 'rgba(0,0,0,1)';
            return;
        }
        paintCtx.globalCompositeOperation = 'source-over';
        const soft = state.softness / 100;
        if (state.tool === 'pencil') {
            paintCtx.lineCap = 'round';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = Math.max(1, state.size * 0.3);
            paintCtx.strokeStyle = strokeColor();
        } else if (state.tool === 'marker') {
            paintCtx.lineCap = 'square';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = state.size * 1.2;
            paintCtx.strokeStyle = strokeColor();
        } else if (state.tool === 'brush') {
            paintCtx.lineCap = 'round';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = state.size;
            paintCtx.shadowBlur = soft * state.size * 0.5;
            paintCtx.shadowColor = strokeColor();
            paintCtx.strokeStyle = strokeColor();
        }
    }

    function sprayAt(x, y) {
        paintCtx.save();
        paintCtx.globalCompositeOperation = 'source-over';
        paintCtx.fillStyle = strokeColor();
        const dots = Math.max(4, Math.floor(state.size / 2));
        const radius = state.size * 0.55;
        for (let i = 0; i < dots; i++) {
            const angle = Math.random() * Math.PI * 2;
            const dist = Math.random() * radius;
            const r = Math.random() * state.size * 0.12 + 0.8;
            paintCtx.beginPath();
            paintCtx.arc(x + Math.cos(angle) * dist, y + Math.sin(angle) * dist, r, 0, Math.PI * 2);
            paintCtx.fill();
        }
        paintCtx.restore();
    }

    function sprayBetween(x0, y0, x1, y1) {
        const dist = Math.hypot(x1 - x0, y1 - y0);
        const steps = Math.max(1, Math.ceil(dist / (state.size * 0.25)));
        for (let i = 0; i <= steps; i++) {
            const t = i / steps;
            sprayAt(x0 + (x1 - x0) * t, y0 + (y1 - y0) * t);
        }
    }

    function drawLine(x0, y0, x1, y1) {
        if (state.tool === 'spray') {
            sprayBetween(x0, y0, x1, y1);
            return;
        }
        paintCtx.save();
        configureBrush();
        paintCtx.beginPath();
        paintCtx.moveTo(x0, y0);
        paintCtx.lineTo(x1, y1);
        paintCtx.stroke();
        paintCtx.restore();
        paintCtx.shadowBlur = 0;
    }

    function pickColorAt(x, y) {
        const dpr = paintCanvas.width / state.naturalW;
        const px = Math.floor(x * dpr);
        const py = Math.floor(y * dpr);
        const paint = paintCtx.getImageData(px, py, 1, 1).data;
        if (paint[3] > 20) {
            return rgbToHex(paint[0], paint[1], paint[2]);
        }
        const line = lineCtx.getImageData(px, py, 1, 1).data;
        const lum = line[0] * 0.299 + line[1] * 0.587 + line[2] * 0.114;
        if (line[3] > 40 && lum < 230) {
            return rgbToHex(line[0], line[1], line[2]);
        }
        return null;
    }

    function floodFill(startX, startY) {
        const dpr = paintCanvas.width / state.naturalW;
        const x = Math.floor(startX * dpr);
        const y = Math.floor(startY * dpr);
        const w = paintCanvas.width;
        const h = paintCanvas.height;

        if (isLineBarrier(x, y)) return;

        const imageData = paintCtx.getImageData(0, 0, w, h);
        const data = imageData.data;
        const startPos = (y * w + x) * 4;
        const startR = data[startPos];
        const startG = data[startPos + 1];
        const startB = data[startPos + 2];
        const startA = data[startPos + 3];
        const fill = fillRgb();

        if (
            startR === fill.r &&
            startG === fill.g &&
            startB === fill.b &&
            Math.abs(startA - fill.a) < 8
        ) {
            return;
        }

        const tolerance = state.fillTolerance;
        const stack = [[x, y]];
        const visited = new Uint8Array(w * h);

        function matchPaint(idx) {
            const r = data[idx];
            const g = data[idx + 1];
            const b = data[idx + 2];
            const a = data[idx + 3];
            return (
                Math.abs(r - startR) <= tolerance &&
                Math.abs(g - startG) <= tolerance &&
                Math.abs(b - startB) <= tolerance &&
                Math.abs(a - startA) <= tolerance
            );
        }

        while (stack.length) {
            const [cx, cy] = stack.pop();
            const key = cy * w + cx;
            if (cx < 0 || cy < 0 || cx >= w || cy >= h || visited[key]) continue;
            if (isLineBarrier(cx, cy)) continue;
            const idx = key * 4;
            if (!matchPaint(idx)) continue;
            visited[key] = 1;
            data[idx] = fill.r;
            data[idx + 1] = fill.g;
            data[idx + 2] = fill.b;
            data[idx + 3] = fill.a;
            stack.push([cx + 1, cy], [cx - 1, cy], [cx, cy + 1], [cx, cy - 1]);
        }

        paintCtx.putImageData(imageData, 0, 0);
    }

    function pointerDown(e) {
        if (!state.ready) return;
        if (state.pointerId !== null) return;
        if (e.button !== undefined && e.button !== 0) return;
        e.preventDefault();
        e.stopPropagation();

        const pt = canvasPoint(e.clientX, e.clientY);

        if (state.tool === 'picker') {
            const picked = pickColorAt(pt.x, pt.y);
            if (picked) {
                setColor(picked);
            }
            setTool('brush');
            return;
        }

        try {
            hitLayer.setPointerCapture(e.pointerId);
        } catch {
            /* */
        }

        state.pointerId = e.pointerId;

        if (state.tool === 'fill') {
            saveUndo();
            floodFill(pt.x, pt.y);
            state.pointerId = null;
            try {
                hitLayer.releasePointerCapture(e.pointerId);
            } catch {
                /* */
            }
            return;
        }

        state.drawing = true;
        saveUndo();
        state.lastX = pt.x;
        state.lastY = pt.y;
        drawLine(pt.x, pt.y, pt.x, pt.y);
    }

    function pointerMove(e) {
        if (!state.ready || state.pointerId !== e.pointerId || !state.drawing) return;
        e.preventDefault();
        const pt = canvasPoint(e.clientX, e.clientY);
        drawLine(state.lastX, state.lastY, pt.x, pt.y);
        state.lastX = pt.x;
        state.lastY = pt.y;
    }

    function pointerUp(e) {
        if (state.pointerId !== null && e.pointerId !== state.pointerId) return;
        state.drawing = false;
        state.pointerId = null;
        try {
            hitLayer.releasePointerCapture(e.pointerId);
        } catch {
            /* */
        }
    }

    function mergeExportCanvas() {
        const out = document.createElement('canvas');
        out.width = paintCanvas.width;
        out.height = paintCanvas.height;
        const ctx = out.getContext('2d');
        ctx.drawImage(paintCanvas, 0, 0);
        ctx.drawImage(lineCanvas, 0, 0);
        return out;
    }

    function exportBlob() {
        return new Promise((resolve, reject) => {
            mergeExportCanvas().toBlob(
                (blob) => (blob ? resolve(blob) : reject(new Error('Dışa aktarılamadı'))),
                'image/png',
                1
            );
        });
    }

    async function uploadExport(format) {
        const blob = await exportBlob();
        const form = new FormData();
        form.append('image', blob, 'boyanmis.png');
        form.append('format', format);
        form.append('_token', config.csrfToken);
        const res = await fetch(config.exportUrl, {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!res.ok) throw new Error('Sunucu hatası');
        return res.blob();
    }

    hitLayer.addEventListener('pointerdown', pointerDown);
    hitLayer.addEventListener('pointermove', pointerMove);
    hitLayer.addEventListener('pointerup', pointerUp);
    hitLayer.addEventListener('pointercancel', pointerUp);
    hitLayer.addEventListener('lostpointercapture', () => {
        state.drawing = false;
        state.pointerId = null;
    });

    hitLayer.addEventListener('mousedown', pointerDown);
    hitLayer.addEventListener('mousemove', pointerMove);
    window.addEventListener('mouseup', pointerUp);

    toolButtons.forEach((btn) => {
        btn.addEventListener('click', (ev) => {
            ev.preventDefault();
            setTool(btn.dataset.paintTool || 'brush');
        });
    });

    swatches.forEach((btn) => {
        btn.addEventListener('click', () => setColor(btn.dataset.paintColor || '#000000'));
    });

    if (colorInput) {
        colorInput.addEventListener('input', () => setColor(colorInput.value));
    }

    if (sizeInput) {
        sizeInput.addEventListener('input', () => {
            state.size = Number(sizeInput.value);
            if (sizeLabel) sizeLabel.textContent = String(state.size);
        });
    }

    if (opacityInput) {
        opacityInput.addEventListener('input', () => {
            state.opacity = Number(opacityInput.value);
            if (opacityLabel) opacityLabel.textContent = `${state.opacity}%`;
        });
    }

    if (softnessInput) {
        softnessInput.addEventListener('input', () => {
            state.softness = Number(softnessInput.value);
            if (softnessLabel) softnessLabel.textContent = `${state.softness}%`;
        });
    }

    if (fillToleranceInput) {
        fillToleranceInput.addEventListener('input', () => {
            state.fillTolerance = Number(fillToleranceInput.value);
            if (fillToleranceLabel) fillToleranceLabel.textContent = String(state.fillTolerance);
        });
    }

    if (randomColorBtn) {
        randomColorBtn.addEventListener('click', randomColor);
    }

    document.querySelectorAll('[data-paint-brush-preset]').forEach((btn) => {
        btn.addEventListener('click', () => applyBrushPreset(btn.dataset.paintBrushPreset || 'normal'));
    });

    document.querySelectorAll('[data-paint-theme]').forEach((btn) => {
        btn.addEventListener('click', () => showThemeStrip(btn.dataset.paintTheme || 'pastel'));
    });

    if (zoomInBtn) {
        zoomInBtn.addEventListener('click', () => {
            state.viewZoom = Math.min(2.5, Math.round((state.viewZoom + 0.15) * 100) / 100);
            applyViewTransform();
        });
    }

    if (zoomOutBtn) {
        zoomOutBtn.addEventListener('click', () => {
            state.viewZoom = Math.max(0.5, Math.round((state.viewZoom - 0.15) * 100) / 100);
            applyViewTransform();
        });
    }

    if (rotateLeftBtn) {
        rotateLeftBtn.addEventListener('click', () => {
            state.viewRotate = (state.viewRotate - 90 + 360) % 360;
            applyViewTransform();
        });
    }

    if (rotateRightBtn) {
        rotateRightBtn.addEventListener('click', () => {
            state.viewRotate = (state.viewRotate + 90) % 360;
            applyViewTransform();
        });
    }

    if (rotateResetBtn) {
        rotateResetBtn.addEventListener('click', () => {
            state.viewRotate = 0;
            applyViewTransform();
        });
    }

    if (orbitToggle) {
        orbitToggle.addEventListener('change', syncOrbitEffects);
    }

    if (glowToggle) {
        glowToggle.addEventListener('change', syncOrbitEffects);
    }

    function afterFullscreenLayout() {
        requestAnimationFrame(() => {
            if (state.ready) {
                fitToView();
                requestAnimationFrame(fitToView);
            }
        });
    }

    if (fullscreenBtn && wrap) {
        fullscreenBtn.addEventListener('click', async () => {
            try {
                if (isCanvasFullscreen()) {
                    await document.exitFullscreen();
                } else {
                    await wrap.requestFullscreen();
                    fullscreenBtn.textContent = 'Tam ekrandan çık';
                    afterFullscreenLayout();
                }
            } catch {
                alert('Tam ekran bu tarayıcıda desteklenmiyor olabilir.');
            }
        });

        const onFullscreenChange = () => {
            if (isCanvasFullscreen()) {
                fullscreenBtn.textContent = 'Tam ekrandan çık';
                afterFullscreenLayout();
            } else {
                fullscreenBtn.textContent = 'Tam ekran tuval';
                afterFullscreenLayout();
            }
        };

        document.addEventListener('fullscreenchange', onFullscreenChange);
        document.addEventListener('webkitfullscreenchange', onFullscreenChange);
    }

    syncOrbitEffects();

    if (undoBtn) undoBtn.addEventListener('click', undo);
    if (redoBtn) redoBtn.addEventListener('click', redo);
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            saveUndo();
            paintCtx.globalCompositeOperation = 'source-over';
            paintCtx.fillStyle = '#ffffff';
            paintCtx.fillRect(0, 0, state.naturalW, state.naturalH);
        });
    }

    if (zoomFitBtn) {
        zoomFitBtn.addEventListener('click', () => {
            state.viewZoom = 1;
            state.viewRotate = 0;
            fitToView();
        });
    }

    if (typeof ResizeObserver !== 'undefined') {
        const ro = new ResizeObserver(() => {
            if (state.ready) fitToView();
        });
        ro.observe(wrap);
    }
    window.addEventListener('resize', () => {
        if (state.ready) fitToView();
    });

    downloadBtns.forEach((btn) => {
        btn.addEventListener('click', async () => {
            const format = btn.dataset.paintDownload || 'png';
            try {
                const blob = await uploadExport(format);
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                const ext = format === 'jpeg' ? 'jpg' : format;
                a.download = `${config.fileBase}-boyanmis.${ext}`;
                a.click();
                URL.revokeObjectURL(url);
            } catch {
                alert('İndirme hazırlanamadı. Lütfen tekrar deneyin.');
            }
        });
    });

    if (printBtn) {
        printBtn.addEventListener('click', async () => {
            try {
                const blob = await exportBlob();
                const url = URL.createObjectURL(blob);
                const win = window.open('', '_blank');
                if (!win) {
                    alert('Yazdırma penceresi açılamadı.');
                    return;
                }
                win.document.write(
                    `<html><head><title>Yazdır</title></head><body style="margin:0;display:flex;justify-content:center;"><img src="${url}" style="max-width:100%;height:auto;" onload="window.print();"></body></html>`
                );
                win.document.close();
            } catch {
                alert('Yazdırma hazırlanamadı.');
            }
        });
    }

    if (emailForm) {
        emailForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const email = emailForm.querySelector('[name="email"]')?.value;
            const format = emailForm.querySelector('[name="format"]')?.value || 'png';
            if (!email) return;
            const submitBtn = document.getElementById('paint-email-submit') || emailForm.querySelector('[type="submit"]');
            const defaultBtnText = submitBtn?.textContent || 'Boyanmış çalışmayı gönder';
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Gönderiliyor…';
            }
            try {
                const blob = await exportBlob();
                const form = new FormData();
                form.append('email', email);
                form.append('format', format);
                form.append('image', blob, 'boyanmis.png');
                form.append('_token', config.csrfToken);
                const res = await fetch(config.emailUrl, {
                    method: 'POST',
                    body: form,
                    credentials: 'same-origin',
                    redirect: 'follow',
                });
                if (res.redirected) {
                    window.location.href = res.url;
                    return;
                }
                if (res.ok) {
                    window.location.reload();
                    return;
                }
                throw new Error();
            } catch {
                alert('E-posta gönderilemedi. SMTP ayarlarını veya adresi kontrol edip tekrar deneyin.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = defaultBtnText;
                }
            }
        });
    }

    async function loadLineArt() {
        try {
            const res = await fetch(config.lineArtUrl, { credentials: 'same-origin' });
            if (!res.ok) {
                showError(
                    res.status === 404
                        ? 'Çizgi görseli bulunamadı. Yönetim panelinde «Dosya» alanını kontrol edin.'
                        : 'Çizgi görseli yüklenemedi. PDF için sunucuda Imagick veya LibreOffice gerekir.'
                );
                return;
            }
            const blob = await res.blob();
            const objectUrl = URL.createObjectURL(blob);
            const img = new Image();
            img.onload = () => {
                const w = img.naturalWidth || img.width;
                const h = img.naturalHeight || img.height;
                resizeCanvases(w, h);
                lineCtx.clearRect(0, 0, state.naturalW, state.naturalH);
                lineCtx.drawImage(img, 0, 0, state.naturalW, state.naturalH);
                applyLineArtTransparency();
                URL.revokeObjectURL(objectUrl);
                state.ready = true;
                if (loader) {
                    loader.classList.add('hidden');
                    loader.style.pointerEvents = 'none';
                }
                requestAnimationFrame(() => {
                    fitToView();
                    requestAnimationFrame(fitToView);
                });
            };
            img.onerror = () => {
                URL.revokeObjectURL(objectUrl);
                showError('Çizgi görseli okunamadı. Dosya biçimi desteklenmiyor olabilir.');
            };
            img.src = objectUrl;
        } catch {
            showError('Çizgi görseli yüklenemedi. Bağlantınızı kontrol edip sayfayı yenileyin.');
        }
    }

    loadLineArt();
    setColor(state.color);
    setTool('brush');
}

window.bootOnlinePaint = bootOnlinePaint;

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootOnlinePaint);
} else {
    bootOnlinePaint();
}

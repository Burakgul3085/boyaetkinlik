/**
 * Görüntülü oda — gelişmiş ortak boyama tuvali (online boya araçları)
 */
const MAX_UNDO = 40;

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

export function initPaintRoomCanvas(options) {
    const {
        lineArtUrl = null,
        onStroke = null,
        onFill = null,
        onClear = null,
        onReady = null,
        enabled = () => true,
    } = options;

    const paintCanvas = document.getElementById('room-paint-canvas');
    const lineCanvas = document.getElementById('room-line-canvas');
    const hitLayer = document.getElementById('room-paint-hit');
    const stage = document.getElementById('room-paint-stage');
    const scaler = document.getElementById('room-canvas-scaler');
    const wrap = document.getElementById('room-canvas-wrap');
    const loader = document.getElementById('room-paint-loader');
    const errBox = document.getElementById('room-paint-error');

    if (!paintCanvas || !lineCanvas || !hitLayer || !stage || !wrap) {
        return null;
    }

    let paintCtx = paintCanvas.getContext('2d', { willReadFrequently: true });
    let lineCtx = lineCanvas.getContext('2d');
    let activeLineArtUrl = lineArtUrl;

    const state = {
        tool: 'brush',
        color: '#ef4444',
        size: 18,
        opacity: 100,
        softness: 35,
        fillTolerance: 40,
        viewZoom: 1,
        drawing: false,
        pointerId: null,
        lastX: 0,
        lastY: 0,
        undoStack: [],
        redoStack: [],
        recentColors: [],
        naturalW: 800,
        naturalH: 600,
        scale: 1,
        linePixels: null,
        ready: false,
        applyingRemote: false,
    };

    const toolButtons = document.querySelectorAll('[data-room-tool]');
    const colorInput = document.getElementById('room-paint-color-custom');
    const colorPreviewSwatch = document.getElementById('room-paint-color-preview-swatch');
    const colorPreviewHex = document.getElementById('room-paint-color-preview-hex');
    const recentColorsEl = document.getElementById('room-recent-colors');
    const themeStripEl = document.getElementById('room-theme-strip');
    const randomColorBtn = document.getElementById('room-random-color');
    const fillToleranceInput = document.getElementById('room-paint-fill-tolerance');
    const fillToleranceLabel = document.getElementById('room-paint-fill-tolerance-label');
    const sizeInput = document.getElementById('room-paint-size');
    const sizeLabel = document.getElementById('room-paint-size-label');
    const opacityInput = document.getElementById('room-paint-opacity');
    const opacityLabel = document.getElementById('room-paint-opacity-label');
    const softnessInput = document.getElementById('room-paint-softness');
    const softnessLabel = document.getElementById('room-paint-softness-label');
    const swatches = document.querySelectorAll('[data-room-color]');
    const undoBtn = document.getElementById('room-paint-undo');
    const redoBtn = document.getElementById('room-paint-redo');
    const clearBtn = document.getElementById('room-paint-clear');
    const zoomFitBtn = document.getElementById('room-paint-zoom-fit');
    const zoomInBtn = document.getElementById('room-paint-zoom-in');
    const zoomOutBtn = document.getElementById('room-paint-zoom-out');
    const zoomLabel = document.getElementById('room-paint-zoom-label');

    function showError(msg) {
        loader?.classList.add('hidden');
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

    function fitToView() {
        if (!state.naturalW || !state.naturalH) return;
        const pad = 8;
        const maxW = Math.max(wrap.clientWidth - pad, 240);
        const maxH = Math.max(wrap.clientHeight - pad, 280);
        let scale = maxW / state.naturalW;
        let displayH = state.naturalH * scale;
        if (displayH > maxH) {
            scale = maxH / state.naturalH;
        }
        state.scale = scale;
        const displayW = Math.max(1, Math.round(state.naturalW * scale));
        displayH = Math.max(1, Math.round(state.naturalH * scale));
        stage.style.width = `${displayW}px`;
        stage.style.height = `${displayH}px`;
        stage.style.aspectRatio = `${state.naturalW} / ${state.naturalH}`;
        if (scaler) {
            scaler.style.transform = `scale(${state.viewZoom})`;
        }
        if (zoomLabel) {
            zoomLabel.textContent = `${Math.round(state.viewZoom * 100)}%`;
        }
    }

    function resizeCanvases(w, h) {
        state.naturalW = w;
        state.naturalH = h;
        setupCanvasContexts();
        paintCtx.fillStyle = '#ffffff';
        paintCtx.fillRect(0, 0, w, h);
        lineCtx.clearRect(0, 0, w, h);
        fitToView();
    }

    function cacheLinePixels() {
        const w = lineCanvas.width;
        const h = lineCanvas.height;
        state.linePixels = w && h ? lineCtx.getImageData(0, 0, w, h).data : null;
    }

    function applyLineArtTransparency() {
        const w = lineCanvas.width;
        const h = lineCanvas.height;
        const imageData = lineCtx.getImageData(0, 0, w, h);
        const d = imageData.data;
        for (let i = 0; i < d.length; i += 4) {
            const lum = d[i] * 0.299 + d[i + 1] * 0.587 + d[i + 2] * 0.114;
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
        if (data[idx + 3] < 64) return false;
        const lum = data[idx] * 0.299 + data[idx + 1] * 0.587 + data[idx + 2] * 0.114;
        return lum < 210;
    }

    function canvasPoint(clientX, clientY) {
        const rect = hitLayer.getBoundingClientRect();
        if (!rect.width || !rect.height) return { x: 0, y: 0 };
        const x = ((clientX - rect.left) / rect.width) * state.naturalW;
        const y = ((clientY - rect.top) / rect.height) * state.naturalH;
        return {
            x: Math.max(0, Math.min(state.naturalW, x)),
            y: Math.max(0, Math.min(state.naturalH, y)),
        };
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

    function strokeColor(opts = {}) {
        const tool = opts.tool ?? state.tool;
        const color = opts.color ?? state.color;
        const opacity = opts.opacity ?? state.opacity;
        if (tool === 'eraser') return 'rgba(0,0,0,1)';
        const c = hexToRgba(color);
        return `rgba(${c.r},${c.g},${c.b},${opacity / 100})`;
    }

    function fillRgb(opts = {}) {
        const c = hexToRgba(opts.color ?? state.color);
        const a = Math.round(((opts.opacity ?? state.opacity) / 100) * 255);
        return { ...c, a };
    }

    function saveUndo() {
        if (state.applyingRemote) return;
        try {
            const snap = paintCtx.getImageData(0, 0, paintCanvas.width, paintCanvas.height);
            state.undoStack.push(snap);
            if (state.undoStack.length > MAX_UNDO) state.undoStack.shift();
            state.redoStack = [];
        } catch { /* */ }
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
            btn.classList.toggle('online-paint-tool--active', btn.dataset.roomTool === tool);
        });
        const cursor = tool === 'fill' ? 'cell' : tool === 'eraser' ? 'grab' : tool === 'picker' ? 'copy' : 'crosshair';
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
        document.querySelectorAll('[data-room-brush-preset]').forEach((btn) => {
            btn.classList.toggle('online-paint-chip--active', btn.dataset.roomBrushPreset === presetKey);
        });
    }

    function updateColorPreview() {
        if (colorPreviewSwatch) colorPreviewSwatch.style.backgroundColor = state.color;
        if (colorPreviewHex) colorPreviewHex.textContent = state.color.toUpperCase();
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
            btn.dataset.roomColor = hex;
            btn.style.backgroundColor = hex;
            btn.addEventListener('click', () => setColor(hex));
            recentColorsEl.appendChild(btn);
        });
    }

    function setColor(hex) {
        state.color = hex;
        if (colorInput) colorInput.value = hex;
        swatches.forEach((s) => {
            s.classList.toggle('online-paint-swatch--active', s.dataset.roomColor?.toLowerCase() === hex.toLowerCase());
        });
        updateColorPreview();
        state.recentColors = state.recentColors.filter((c) => c.toLowerCase() !== hex.toLowerCase());
        state.recentColors.unshift(hex);
        if (state.recentColors.length > 8) state.recentColors.pop();
        renderRecentColors();
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
            btn.dataset.roomColor = hex;
            btn.style.backgroundColor = hex;
            btn.addEventListener('click', () => setColor(hex));
            themeStripEl.appendChild(btn);
        });
        setColor(colors[0]);
        document.querySelectorAll('[data-room-theme]').forEach((btn) => {
            btn.classList.toggle('online-paint-chip--active', btn.dataset.roomTheme === themeKey);
        });
    }

    function configureBrush(opts = {}) {
        const tool = opts.tool ?? state.tool;
        const size = opts.size ?? state.size;
        const softness = opts.softness ?? state.softness;
        paintCtx.shadowBlur = 0;
        if (tool === 'eraser') {
            paintCtx.globalCompositeOperation = 'destination-out';
            paintCtx.lineCap = 'round';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = size;
            paintCtx.strokeStyle = 'rgba(0,0,0,1)';
            return;
        }
        paintCtx.globalCompositeOperation = 'source-over';
        const soft = softness / 100;
        if (tool === 'pencil') {
            paintCtx.lineCap = 'round';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = Math.max(1, size * 0.3);
            paintCtx.strokeStyle = strokeColor(opts);
        } else if (tool === 'marker') {
            paintCtx.lineCap = 'square';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = size * 1.2;
            paintCtx.strokeStyle = strokeColor(opts);
        } else if (tool === 'brush') {
            paintCtx.lineCap = 'round';
            paintCtx.lineJoin = 'round';
            paintCtx.lineWidth = size;
            paintCtx.shadowBlur = soft * size * 0.5;
            paintCtx.shadowColor = strokeColor(opts);
            paintCtx.strokeStyle = strokeColor(opts);
        }
    }

    function sprayAt(x, y, opts = {}) {
        const size = opts.size ?? state.size;
        paintCtx.save();
        paintCtx.globalCompositeOperation = 'source-over';
        paintCtx.fillStyle = strokeColor(opts);
        const dots = Math.max(4, Math.floor(size / 2));
        const radius = size * 0.55;
        for (let i = 0; i < dots; i++) {
            const angle = Math.random() * Math.PI * 2;
            const dist = Math.random() * radius;
            const r = Math.random() * size * 0.12 + 0.8;
            paintCtx.beginPath();
            paintCtx.arc(x + Math.cos(angle) * dist, y + Math.sin(angle) * dist, r, 0, Math.PI * 2);
            paintCtx.fill();
        }
        paintCtx.restore();
    }

    function sprayBetween(x0, y0, x1, y1, opts = {}) {
        const size = opts.size ?? state.size;
        const dist = Math.hypot(x1 - x0, y1 - y0);
        const steps = Math.max(1, Math.ceil(dist / (size * 0.25)));
        for (let i = 0; i <= steps; i++) {
            const t = i / steps;
            sprayAt(x0 + (x1 - x0) * t, y0 + (y1 - y0) * t, opts);
        }
    }

    function drawLine(x0, y0, x1, y1, opts = {}) {
        const tool = opts.tool ?? state.tool;
        if (tool === 'spray') {
            sprayBetween(x0, y0, x1, y1, opts);
            return;
        }
        paintCtx.save();
        configureBrush(opts);
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
        if (paint[3] > 20) return rgbToHex(paint[0], paint[1], paint[2]);
        const line = lineCtx.getImageData(px, py, 1, 1).data;
        const lum = line[0] * 0.299 + line[1] * 0.587 + line[2] * 0.114;
        if (line[3] > 40 && lum < 230) return rgbToHex(line[0], line[1], line[2]);
        return null;
    }

    function floodFill(startX, startY, opts = {}) {
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
        const fill = fillRgb(opts);
        const tolerance = opts.tolerance ?? state.fillTolerance;

        if (
            startR === fill.r && startG === fill.g && startB === fill.b
            && Math.abs(startA - fill.a) < 8
        ) return;

        const stack = [[x, y]];
        const visited = new Uint8Array(w * h);

        function matchPaint(idx) {
            return (
                Math.abs(data[idx] - startR) <= tolerance
                && Math.abs(data[idx + 1] - startG) <= tolerance
                && Math.abs(data[idx + 2] - startB) <= tolerance
                && Math.abs(data[idx + 3] - startA) <= tolerance
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

    function emitStroke(x0, y0, x1, y1) {
        if (state.applyingRemote || !enabled()) return;
        onStroke?.({
            x0, y0, x1, y1,
            color: state.color,
            size: state.size,
            tool: state.tool,
            opacity: state.opacity,
            softness: state.softness,
        });
    }

    function emitFill(x, y) {
        if (state.applyingRemote || !enabled()) return;
        onFill?.({
            x, y,
            color: state.color,
            opacity: state.opacity,
            tolerance: state.fillTolerance,
        });
    }

    function pointerDown(e) {
        if (!state.ready || !enabled()) return;
        if (state.pointerId !== null) return;
        if (e.button !== undefined && e.button !== 0) return;
        e.preventDefault();

        const pt = canvasPoint(e.clientX, e.clientY);

        if (state.tool === 'picker') {
            const picked = pickColorAt(pt.x, pt.y);
            if (picked) setColor(picked);
            setTool('brush');
            return;
        }

        try { hitLayer.setPointerCapture(e.pointerId); } catch { /* */ }
        state.pointerId = e.pointerId;

        if (state.tool === 'fill') {
            saveUndo();
            floodFill(pt.x, pt.y);
            emitFill(pt.x, pt.y);
            state.pointerId = null;
            try { hitLayer.releasePointerCapture(e.pointerId); } catch { /* */ }
            return;
        }

        state.drawing = true;
        saveUndo();
        state.lastX = pt.x;
        state.lastY = pt.y;
        drawLine(pt.x, pt.y, pt.x, pt.y);
        emitStroke(pt.x, pt.y, pt.x, pt.y);
    }

    function pointerMove(e) {
        if (!state.drawing || state.pointerId !== e.pointerId) return;
        e.preventDefault();
        const pt = canvasPoint(e.clientX, e.clientY);
        drawLine(state.lastX, state.lastY, pt.x, pt.y);
        emitStroke(state.lastX, state.lastY, pt.x, pt.y);
        state.lastX = pt.x;
        state.lastY = pt.y;
    }

    function pointerUp(e) {
        if (state.pointerId !== null && e.pointerId !== state.pointerId) return;
        state.drawing = false;
        state.pointerId = null;
        try { hitLayer.releasePointerCapture(e.pointerId); } catch { /* */ }
    }

    function markReady() {
        state.ready = true;
        loader?.classList.add('hidden');
        fitToView();
        onReady?.();
    }

    function loadLineArt() {
        if (!activeLineArtUrl) {
            resizeCanvases(800, 600);
            markReady();
            return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const maxW = 1200;
            const scale = Math.min(1, maxW / img.naturalWidth);
            const w = Math.round(img.naturalWidth * scale);
            const h = Math.round(img.naturalHeight * scale);
            resizeCanvases(w, h);
            lineCtx.clearRect(0, 0, w, h);
            lineCtx.drawImage(img, 0, 0, w, h);
            applyLineArtTransparency();
            markReady();
        };
        img.onerror = () => {
            showError('Çizgi görseli yüklenemedi. Sayfayı yenileyin.');
            resizeCanvases(800, 600);
            markReady();
        };
        const sep = activeLineArtUrl.includes('?') ? '&' : '?';
        img.src = `${activeLineArtUrl}${sep}_=${Date.now()}`;
    }

    function reloadLineArt(newUrl) {
        activeLineArtUrl = newUrl || null;
        state.ready = false;
        state.undoStack = [];
        state.redoStack = [];
        loader?.classList.remove('hidden');
        errBox?.classList.add('hidden');
        if (state.naturalW > 0 && state.naturalH > 0) {
            paintCtx.fillStyle = '#ffffff';
            paintCtx.fillRect(0, 0, state.naturalW, state.naturalH);
            lineCtx.clearRect(0, 0, state.naturalW, state.naturalH);
        }
        loadLineArt();
    }

    toolButtons.forEach((btn) => {
        btn.addEventListener('click', () => setTool(btn.dataset.roomTool || 'brush'));
    });

    swatches.forEach((btn) => {
        btn.addEventListener('click', () => setColor(btn.dataset.roomColor || '#ef4444'));
    });

    document.querySelectorAll('[data-room-brush-preset]').forEach((btn) => {
        btn.addEventListener('click', () => applyBrushPreset(btn.dataset.roomBrushPreset));
    });

    document.querySelectorAll('[data-room-theme]').forEach((btn) => {
        btn.addEventListener('click', () => showThemeStrip(btn.dataset.roomTheme));
    });

    colorInput?.addEventListener('input', (e) => setColor(e.target.value));
    randomColorBtn?.addEventListener('click', () => {
        const hex = `#${Math.floor(Math.random() * 0xffffff).toString(16).padStart(6, '0')}`;
        setColor(hex);
    });

    sizeInput?.addEventListener('input', (e) => {
        state.size = parseInt(e.target.value, 10) || 18;
        if (sizeLabel) sizeLabel.textContent = String(state.size);
    });

    opacityInput?.addEventListener('input', (e) => {
        state.opacity = parseInt(e.target.value, 10) || 100;
        if (opacityLabel) opacityLabel.textContent = `${state.opacity}%`;
    });

    softnessInput?.addEventListener('input', (e) => {
        state.softness = parseInt(e.target.value, 10) || 0;
        if (softnessLabel) softnessLabel.textContent = `${state.softness}%`;
    });

    fillToleranceInput?.addEventListener('input', (e) => {
        state.fillTolerance = parseInt(e.target.value, 10) || 40;
        if (fillToleranceLabel) fillToleranceLabel.textContent = String(state.fillTolerance);
    });

    undoBtn?.addEventListener('click', undo);
    redoBtn?.addEventListener('click', redo);

    clearBtn?.addEventListener('click', () => {
        if (!enabled()) return;
        saveUndo();
        paintCtx.fillStyle = '#ffffff';
        paintCtx.fillRect(0, 0, state.naturalW, state.naturalH);
        onClear?.();
    });

    zoomFitBtn?.addEventListener('click', () => {
        state.viewZoom = 1;
        fitToView();
    });

    zoomInBtn?.addEventListener('click', () => {
        state.viewZoom = Math.min(2.5, state.viewZoom + 0.15);
        fitToView();
    });

    zoomOutBtn?.addEventListener('click', () => {
        state.viewZoom = Math.max(0.5, state.viewZoom - 0.15);
        fitToView();
    });

    hitLayer.addEventListener('pointerdown', pointerDown);
    hitLayer.addEventListener('pointermove', pointerMove);
    hitLayer.addEventListener('pointerup', pointerUp);
    hitLayer.addEventListener('pointercancel', pointerUp);

    window.addEventListener('resize', () => {
        if (state.ready) fitToView();
    });

    setTool('brush');
    setColor('#ef4444');
    applyBrushPreset('normal');
    loadLineArt();

    return {
        drawRemoteStroke(stroke) {
            state.applyingRemote = true;
            drawLine(stroke.x0, stroke.y0, stroke.x1, stroke.y1, stroke);
            state.applyingRemote = false;
        },
        applyRemoteFill(fill) {
            state.applyingRemote = true;
            saveUndo();
            floodFill(fill.x, fill.y, fill);
            state.applyingRemote = false;
        },
        clear(local = true) {
            paintCtx.fillStyle = '#ffffff';
            paintCtx.fillRect(0, 0, state.naturalW, state.naturalH);
            if (local && !state.applyingRemote) onClear?.();
        },
        getSnapshot() {
            if (!state.ready) return null;
            try {
                return paintCanvas.toDataURL('image/jpeg', 0.72);
            } catch {
                return null;
            }
        },
        applySnapshot(dataUrl) {
            if (!dataUrl || !state.ready) return;
            const img = new Image();
            img.onload = () => {
                state.applyingRemote = true;
                paintCtx.fillStyle = '#ffffff';
                paintCtx.fillRect(0, 0, state.naturalW, state.naturalH);
                paintCtx.drawImage(img, 0, 0, state.naturalW, state.naturalH);
                state.applyingRemote = false;
            };
            img.src = dataUrl;
        },
        isReady: () => state.ready,
        fitToView,
        reloadLineArt,
    };
}

export { COLOR_THEMES, BRUSH_PRESETS };

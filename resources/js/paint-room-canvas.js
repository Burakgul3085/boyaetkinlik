/**
 * Görüntülü oda — ortak boyama tuvali
 */
const COLORS = [
    '#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4',
    '#3b82f6', '#8b5cf6', '#ec4899', '#78350f', '#1e293b',
    '#ffffff', '#94a3b8',
];

export function initPaintRoomCanvas(options) {
    const {
        lineArtUrl = null,
        onStroke = null,
        onClear = null,
        enabled = () => true,
    } = options;

    const paintCanvas = document.getElementById('room-paint-canvas');
    const lineCanvas = document.getElementById('room-line-canvas');
    const hitLayer = document.getElementById('room-paint-hit');
    const stage = document.getElementById('room-paint-stage');
    const loader = document.getElementById('room-paint-loader');
    const toolBtns = document.querySelectorAll('[data-room-tool]');
    const colorBtns = document.querySelectorAll('[data-room-color]');
    const colorInput = document.getElementById('room-paint-color');
    const sizeInput = document.getElementById('room-paint-size');
    const sizeLabel = document.getElementById('room-paint-size-label');
    const clearBtn = document.getElementById('room-paint-clear');

    if (!paintCanvas || !lineCanvas || !hitLayer || !stage) {
        return null;
    }

    const paintCtx = paintCanvas.getContext('2d', { willReadFrequently: true });
    const lineCtx = lineCanvas.getContext('2d');

    const state = {
        tool: 'brush',
        color: '#ef4444',
        size: 16,
        naturalW: 800,
        naturalH: 600,
        drawing: false,
        pointerId: null,
        lastX: 0,
        lastY: 0,
        ready: false,
        applyingRemote: false,
    };

    function setTool(tool) {
        state.tool = tool;
        toolBtns.forEach((b) => {
            b.classList.toggle('paint-room-tool--active', b.dataset.roomTool === tool);
        });
    }

    function setColor(hex) {
        state.color = hex;
        if (colorInput) colorInput.value = hex;
        colorBtns.forEach((b) => {
            b.classList.toggle('paint-room-swatch--active', b.dataset.roomColor === hex);
        });
    }

    function setupCanvasSize(w, h) {
        const dpr = Math.min(window.devicePixelRatio || 1, 2);
        state.naturalW = w;
        state.naturalH = h;
        [paintCanvas, lineCanvas].forEach((c) => {
            c.width = Math.floor(w * dpr);
            c.height = Math.floor(h * dpr);
            c.style.width = '100%';
            c.style.height = 'auto';
            c.style.aspectRatio = `${w} / ${h}`;
        });
        paintCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        lineCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        stage.style.aspectRatio = `${w} / ${h}`;
        paintCtx.clearRect(0, 0, w, h);
        lineCtx.clearRect(0, 0, w, h);
    }

    function canvasPoint(clientX, clientY) {
        const rect = hitLayer.getBoundingClientRect();
        const x = ((clientX - rect.left) / rect.width) * state.naturalW;
        const y = ((clientY - rect.top) / rect.height) * state.naturalH;
        return {
            x: Math.max(0, Math.min(state.naturalW, x)),
            y: Math.max(0, Math.min(state.naturalH, y)),
        };
    }

    function strokeColor(opacity = 1) {
        const hex = state.color.replace('#', '');
        const r = parseInt(hex.slice(0, 2), 16);
        const g = parseInt(hex.slice(2, 4), 16);
        const b = parseInt(hex.slice(4, 6), 16);
        return `rgba(${r},${g},${b},${opacity})`;
    }

    function drawStroke(x0, y0, x1, y1, opts = {}) {
        const tool = opts.tool || state.tool;
        const size = opts.size ?? state.size;
        const color = opts.color || state.color;

        paintCtx.save();
        if (tool === 'eraser') {
            paintCtx.globalCompositeOperation = 'destination-out';
            paintCtx.strokeStyle = 'rgba(0,0,0,1)';
        } else {
            paintCtx.globalCompositeOperation = 'source-over';
            const hex = color.replace('#', '');
            const r = parseInt(hex.slice(0, 2), 16);
            const g = parseInt(hex.slice(2, 4), 16);
            const b = parseInt(hex.slice(4, 6), 16);
            paintCtx.strokeStyle = `rgba(${r},${g},${b},1)`;
        }
        paintCtx.lineCap = 'round';
        paintCtx.lineJoin = 'round';
        paintCtx.lineWidth = size;
        paintCtx.beginPath();
        paintCtx.moveTo(x0, y0);
        paintCtx.lineTo(x1, y1);
        paintCtx.stroke();
        paintCtx.restore();
    }

    function emitStroke(x0, y0, x1, y1) {
        if (state.applyingRemote || !enabled()) return;
        onStroke?.({
            x0, y0, x1, y1,
            color: state.color,
            size: state.size,
            tool: state.tool,
        });
    }

    function pointerDown(e) {
        if (!state.ready || !enabled()) return;
        if (state.pointerId !== null) return;
        if (e.button !== undefined && e.button !== 0) return;
        e.preventDefault();

        const pt = canvasPoint(e.clientX, e.clientY);
        try { hitLayer.setPointerCapture(e.pointerId); } catch (_) { /* */ }
        state.pointerId = e.pointerId;
        state.drawing = true;
        state.lastX = pt.x;
        state.lastY = pt.y;
        drawStroke(pt.x, pt.y, pt.x, pt.y);
        emitStroke(pt.x, pt.y, pt.x, pt.y);
    }

    function pointerMove(e) {
        if (!state.drawing || state.pointerId !== e.pointerId) return;
        e.preventDefault();
        const pt = canvasPoint(e.clientX, e.clientY);
        drawStroke(state.lastX, state.lastY, pt.x, pt.y);
        emitStroke(state.lastX, state.lastY, pt.x, pt.y);
        state.lastX = pt.x;
        state.lastY = pt.y;
    }

    function pointerUp(e) {
        if (state.pointerId !== null && e.pointerId !== state.pointerId) return;
        state.drawing = false;
        state.pointerId = null;
        try { hitLayer.releasePointerCapture(e.pointerId); } catch (_) { /* */ }
    }

    async function loadLineArt() {
        if (!lineArtUrl) {
            setupCanvasSize(800, 600);
            state.ready = true;
            loader?.classList.add('hidden');
            return;
        }

        const img = new Image();
        img.crossOrigin = 'anonymous';
        img.onload = () => {
            const maxW = 900;
            const scale = Math.min(1, maxW / img.naturalWidth);
            const w = Math.round(img.naturalWidth * scale);
            const h = Math.round(img.naturalHeight * scale);
            setupCanvasSize(w, h);
            lineCtx.drawImage(img, 0, 0, w, h);
            state.ready = true;
            loader?.classList.add('hidden');
        };
        img.onerror = () => {
            setupCanvasSize(800, 600);
            state.ready = true;
            loader?.classList.add('hidden');
        };
        img.src = lineArtUrl;
    }

    toolBtns.forEach((btn) => {
        btn.addEventListener('click', () => setTool(btn.dataset.roomTool || 'brush'));
    });

    colorBtns.forEach((btn) => {
        btn.addEventListener('click', () => setColor(btn.dataset.roomColor || '#ef4444'));
    });

    colorInput?.addEventListener('input', (e) => setColor(e.target.value));
    sizeInput?.addEventListener('input', (e) => {
        state.size = parseInt(e.target.value, 10) || 16;
        if (sizeLabel) sizeLabel.textContent = `${state.size}px`;
    });

    clearBtn?.addEventListener('click', () => {
        if (!enabled()) return;
        paintCtx.clearRect(0, 0, state.naturalW, state.naturalH);
        onClear?.();
    });

    hitLayer.addEventListener('pointerdown', pointerDown);
    hitLayer.addEventListener('pointermove', pointerMove);
    hitLayer.addEventListener('pointerup', pointerUp);
    hitLayer.addEventListener('pointercancel', pointerUp);

    setTool('brush');
    setColor('#ef4444');
    loadLineArt();

    return {
        drawRemoteStroke(stroke) {
            state.applyingRemote = true;
            drawStroke(stroke.x0, stroke.y0, stroke.x1, stroke.y1, stroke);
            state.applyingRemote = false;
        },
        clear(local = true) {
            paintCtx.clearRect(0, 0, state.naturalW, state.naturalH);
            if (local && !state.applyingRemote) onClear?.();
        },
        getSnapshot() {
            if (!state.ready) return null;
            try {
                return paintCanvas.toDataURL('image/jpeg', 0.72);
            } catch (_) {
                return null;
            }
        },
        applySnapshot(dataUrl) {
            if (!dataUrl) return;
            const img = new Image();
            img.onload = () => {
                state.applyingRemote = true;
                paintCtx.clearRect(0, 0, state.naturalW, state.naturalH);
                paintCtx.drawImage(img, 0, 0, state.naturalW, state.naturalH);
                state.applyingRemote = false;
            };
            img.src = dataUrl;
        },
        isReady: () => state.ready,
    };
}

export { COLORS };

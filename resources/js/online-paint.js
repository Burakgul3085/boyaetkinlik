/**
 * Online Boya — çizgi katmanı üstte, boyama altta; dışa aktarma sunucuya PNG gönderilir.
 */
const PRESET_COLORS = [
    '#000000', '#ffffff', '#ef4444', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6',
    '#8b5cf6', '#ec4899', '#78350f', '#a16207', '#166534', '#0e7490', '#1d4ed8', '#6d28d9',
    '#9f1239', '#fca5a5', '#fdba74', '#fde047', '#86efac', '#67e8f9', '#93c5fd', '#c4b5fd',
];

const MAX_UNDO = 40;

function initOnlinePaint(config) {
    const paintCanvas = document.getElementById('paint-canvas');
    const lineCanvas = document.getElementById('line-canvas');
    const stage = document.getElementById('canvas-stage');
    const wrap = document.getElementById('canvas-wrap');
    const loader = document.getElementById('paint-loader');
    const errBox = document.getElementById('paint-error');

    if (!paintCanvas || !lineCanvas || !stage || !config?.lineArtUrl) {
        return;
    }

    let paintCtx = paintCanvas.getContext('2d', { willReadFrequently: true });
    let lineCtx = lineCanvas.getContext('2d');

    const state = {
        tool: 'brush',
        color: '#ef4444',
        size: 18,
        drawing: false,
        lastX: 0,
        lastY: 0,
        undoStack: [],
        redoStack: [],
        naturalW: 0,
        naturalH: 0,
        scale: 1,
    };

    const toolButtons = document.querySelectorAll('[data-paint-tool]');
    const colorInput = document.getElementById('paint-color-custom');
    const sizeInput = document.getElementById('paint-size');
    const sizeLabel = document.getElementById('paint-size-label');
    const swatches = document.querySelectorAll('[data-paint-color]');
    const undoBtn = document.getElementById('paint-undo');
    const redoBtn = document.getElementById('paint-redo');
    const clearBtn = document.getElementById('paint-clear');
    const zoomFitBtn = document.getElementById('paint-zoom-fit');
    const downloadBtns = document.querySelectorAll('[data-paint-download]');
    const printBtn = document.getElementById('paint-print');
    const emailForm = document.getElementById('paint-email-form');

    function showError(msg) {
        if (loader) loader.classList.add('hidden');
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

    function resizeCanvases(w, h) {
        state.naturalW = w;
        state.naturalH = h;
        setupCanvasContexts();
        paintCtx.fillStyle = '#ffffff';
        paintCtx.fillRect(0, 0, w, h);
        fitToView();
    }

    function fitToView() {
        if (!wrap || !state.naturalW) return;
        const pad = 24;
        const maxW = Math.max(wrap.clientWidth - pad, 120);
        const maxH = Math.max(wrap.clientHeight - pad, 120);
        state.scale = Math.min(maxW / state.naturalW, maxH / state.naturalH, 1);
        const displayW = Math.round(state.naturalW * state.scale);
        const displayH = Math.round(state.naturalH * state.scale);
        stage.style.width = `${displayW}px`;
        stage.style.height = `${displayH}px`;
    }

    function canvasPoint(clientX, clientY) {
        const rect = stage.getBoundingClientRect();
        const x = ((clientX - rect.left) / rect.width) * state.naturalW;
        const y = ((clientY - rect.top) / rect.height) * state.naturalH;
        return {
            x: Math.max(0, Math.min(state.naturalW, x)),
            y: Math.max(0, Math.min(state.naturalH, y)),
        };
    }

    function saveUndo() {
        const snap = paintCtx.getImageData(0, 0, paintCanvas.width, paintCanvas.height);
        state.undoStack.push(snap);
        if (state.undoStack.length > MAX_UNDO) state.undoStack.shift();
        state.redoStack = [];
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
        paintCanvas.style.cursor = tool === 'fill' ? 'cell' : tool === 'eraser' ? 'grab' : 'crosshair';
    }

    function drawLine(x0, y0, x1, y1) {
        paintCtx.save();
        paintCtx.lineCap = 'round';
        paintCtx.lineJoin = 'round';
        paintCtx.lineWidth = state.size;
        if (state.tool === 'eraser') {
            paintCtx.globalCompositeOperation = 'destination-out';
            paintCtx.strokeStyle = 'rgba(0,0,0,1)';
        } else {
            paintCtx.globalCompositeOperation = 'source-over';
            paintCtx.strokeStyle = state.color;
        }
        paintCtx.beginPath();
        paintCtx.moveTo(x0, y0);
        paintCtx.lineTo(x1, y1);
        paintCtx.stroke();
        paintCtx.restore();
    }

    function floodFill(startX, startY) {
        const dpr = paintCanvas.width / state.naturalW;
        const x = Math.floor(startX * dpr);
        const y = Math.floor(startY * dpr);
        const w = paintCanvas.width;
        const h = paintCanvas.height;
        const imageData = paintCtx.getImageData(0, 0, w, h);
        const data = imageData.data;

        const startPos = (y * w + x) * 4;
        const startR = data[startPos];
        const startG = data[startPos + 1];
        const startB = data[startPos + 2];
        const startA = data[startPos + 3];

        const fill = hexToRgba(state.color);
        if (
            startR === fill.r &&
            startG === fill.g &&
            startB === fill.b &&
            startA === fill.a
        ) {
            return;
        }

        const tolerance = 32;
        const stack = [[x, y]];
        const visited = new Uint8Array(w * h);

        function match(idx) {
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
            const idx = key * 4;
            if (!match(idx)) continue;
            visited[key] = 1;
            data[idx] = fill.r;
            data[idx + 1] = fill.g;
            data[idx + 2] = fill.b;
            data[idx + 3] = 255;
            stack.push([cx + 1, cy], [cx - 1, cy], [cx, cy + 1], [cx, cy - 1]);
        }

        paintCtx.putImageData(imageData, 0, 0);
    }

    function hexToRgba(hex) {
        const h = hex.replace('#', '');
        const full = h.length === 3 ? h.split('').map((c) => c + c).join('') : h;
        const n = parseInt(full, 16);
        return { r: (n >> 16) & 255, g: (n >> 8) & 255, b: n & 255, a: 255 };
    }

    function pointerDown(e) {
        if (e.button !== undefined && e.button !== 0) return;
        e.preventDefault();
        const pt = canvasPoint(e.clientX, e.clientY);
        if (state.tool === 'fill') {
            saveUndo();
            floodFill(pt.x, pt.y);
            return;
        }
        state.drawing = true;
        saveUndo();
        state.lastX = pt.x;
        state.lastY = pt.y;
        drawLine(pt.x, pt.y, pt.x, pt.y);
    }

    function pointerMove(e) {
        if (!state.drawing) return;
        e.preventDefault();
        const pt = canvasPoint(e.clientX, e.clientY);
        drawLine(state.lastX, state.lastY, pt.x, pt.y);
        state.lastX = pt.x;
        state.lastY = pt.y;
    }

    function pointerUp() {
        state.drawing = false;
    }

    function mergeExportCanvas() {
        const dpr = paintCanvas.width / state.naturalW;
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
            headers: { Accept: 'application/octet-stream' },
        });
        if (!res.ok) throw new Error('Sunucu hatası');
        return res.blob();
    }

    stage.addEventListener('mousedown', pointerDown);
    stage.addEventListener('mousemove', pointerMove);
    window.addEventListener('mouseup', pointerUp);
    stage.addEventListener('touchstart', (e) => {
        if (e.touches[0]) pointerDown(e.touches[0]);
    }, { passive: false });
    stage.addEventListener('touchmove', (e) => {
        if (e.touches[0]) pointerMove(e.touches[0]);
    }, { passive: false });
    window.addEventListener('touchend', pointerUp);

    toolButtons.forEach((btn) => {
        btn.addEventListener('click', () => setTool(btn.dataset.paintTool));
    });

    swatches.forEach((btn) => {
        btn.addEventListener('click', () => {
            state.color = btn.dataset.paintColor;
            if (colorInput) colorInput.value = state.color;
            swatches.forEach((s) => s.classList.toggle('online-paint-swatch--active', s === btn));
        });
    });

    if (colorInput) {
        colorInput.addEventListener('input', () => {
            state.color = colorInput.value;
        });
    }

    if (sizeInput) {
        sizeInput.addEventListener('input', () => {
            state.size = Number(sizeInput.value);
            if (sizeLabel) sizeLabel.textContent = String(state.size);
        });
    }

    if (undoBtn) undoBtn.addEventListener('click', undo);
    if (redoBtn) redoBtn.addEventListener('click', redo);
    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            saveUndo();
            paintCtx.fillStyle = '#ffffff';
            paintCtx.fillRect(0, 0, state.naturalW, state.naturalH);
        });
    }

    if (zoomFitBtn) zoomFitBtn.addEventListener('click', fitToView);
    window.addEventListener('resize', fitToView);

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
            const submitBtn = emailForm.querySelector('[type="submit"]');
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
                    redirect: 'follow',
                });
                if (res.redirected || res.ok) {
                    window.location.href = res.url;
                    return;
                }
                throw new Error();
            } catch {
                alert('E-posta gönderilemedi.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'E-postaya gönder';
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
                URL.revokeObjectURL(objectUrl);
                if (loader) loader.classList.add('hidden');
                requestAnimationFrame(fitToView);
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

    setTool('brush');
    if (swatches[0]) swatches[0].classList.add('online-paint-swatch--active');
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.__ONLINE_PAINT__) {
        initOnlinePaint(window.__ONLINE_PAINT__);
    }
});

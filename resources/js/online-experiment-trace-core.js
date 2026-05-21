/**
 * Çizgi tamamlama stüdyosu — paylaşılan motor (desen / harf / sayı)
 */
export function initTraceStudio(config) {
    const app = document.getElementById('online-exp-app');
    if (!app) return;

    const TOTAL_STEPS = 4;
    const TARGET_PCT = 0.88;
    const LOGICAL_W = 560;
    const LOGICAL_H = 360;
    const RAW_PATTERNS = config.patterns || {};
    const steps = config.steps || [];
    const variant = config.variant || 'shape';
    const defaultPattern = config.defaultPattern || Object.keys(RAW_PATTERNS)[0];
    const resetPattern = config.resetPattern || defaultPattern;
    const downloadPrefix = config.downloadPrefix || 'cizgi-calismasi';
    const celebrate = config.celebrate || { emoji: '⭐', title: 'Harika!', sub: 'Çizgiyi başarıyla tamamladın' };
    const resultCopy = config.resultCopy || {
        title: 'Çizgi tamamlandı!',
        text: 'Çizgi çalışması başarıyla bitti.',
        hint: 'Başka desen dene, PNG kaydet veya boyama sayfalarına git.',
    };

    let stepIndex = 0;
    let currentPattern = defaultPattern;
    let samples = [];
    let hitCount = 0;
    let drawing = false;
    let completed = false;
    let brushRadius = 14;
    let brushSize = 22;
    let strokeTrail = [];
    let patternsNormalized = {};
    let dpr = 1;
    let rafPending = false;
    let dashAnimActive = false;
    let nextHintIndex = 0;

    const el = {
        progressBar: document.getElementById('exp-progress-bar'),
        badge: document.getElementById('exp-step-badge'),
        title: document.getElementById('exp-step-title'),
        text: document.getElementById('exp-step-text'),
        checklist: document.getElementById('exp-checklist'),
        btnPrev: document.getElementById('exp-btn-prev'),
        btnNext: document.getElementById('exp-btn-next'),
        hint: document.getElementById('exp-stage-hint'),
        sideBody: document.getElementById('exp-side-body'),
        sideActions: document.getElementById('exp-side-actions'),
        btnRetry: document.getElementById('exp-btn-retry'),
        btnScreenshot: document.getElementById('exp-btn-screenshot'),
        canvas: document.getElementById('exp-trace-canvas'),
        canvasWrap: document.getElementById('exp-trace-canvas-wrap'),
        progress: document.getElementById('exp-trace-progress'),
        patterns: document.getElementById('exp-trace-patterns'),
        toolbar: document.getElementById('exp-trace-toolbar'),
        clearBtn: document.getElementById('exp-trace-clear'),
        ringFill: document.getElementById('exp-trace-ring-fill'),
        ringPct: document.getElementById('exp-trace-ring-pct'),
        celebrate: document.getElementById('exp-trace-celebrate'),
        celebrateEmoji: document.getElementById('exp-trace-celebrate-emoji'),
        celebrateTitle: document.getElementById('exp-trace-celebrate-title'),
        celebrateSub: document.getElementById('exp-trace-celebrate-sub'),
        startBtn: document.getElementById('exp-trace-start'),
        workspace: document.getElementById('exp-trace-workspace'),
    };

    let ctx = null;

    if (el.celebrateEmoji) el.celebrateEmoji.textContent = celebrate.emoji;
    if (el.celebrateTitle) el.celebrateTitle.textContent = celebrate.title;
    if (el.celebrateSub) el.celebrateSub.textContent = celebrate.sub;

    buildPatternCards();
    initPatterns();
    setupCanvas();
    drawAllPreviews();
    bindEvents();
    selectPattern(defaultPattern);
    renderStep();

    function patternSegments(key) {
        const p = RAW_PATTERNS[key];
        if (!p) return [];
        if (p.segments) return p.segments;
        if (p.points) return [p.points];
        return [];
    }

    function initPatterns() {
        Object.keys(RAW_PATTERNS).forEach((key) => {
            const segs = patternSegments(key);
            patternsNormalized[key] = segs.map((seg) => normalizePoints(seg, LOGICAL_W, LOGICAL_H, 36));
        });
    }

    function normalizePoints(points, w, h, pad) {
        let minX = Infinity,
            maxX = -Infinity,
            minY = Infinity,
            maxY = -Infinity;
        points.forEach((p) => {
            minX = Math.min(minX, p[0]);
            maxX = Math.max(maxX, p[0]);
            minY = Math.min(minY, p[1]);
            maxY = Math.max(maxY, p[1]);
        });
        const rw = maxX - minX || 1;
        const rh = maxY - minY || 1;
        const scale = Math.min((w - pad * 2) / rw, (h - pad * 2) / rh);
        const ox = (w - rw * scale) / 2;
        const oy = (h - rh * scale) / 2;
        return points.map((p) => [ox + (p[0] - minX) * scale, oy + (p[1] - minY) * scale]);
    }

    function buildPatternCards() {
        if (!el.patterns) return;
        el.patterns.innerHTML = '';
        const keys = Object.keys(RAW_PATTERNS);
        const sorted =
            variant === 'letter'
                ? keys.sort((a, b) => a.localeCompare(b, 'tr'))
                : variant === 'number'
                  ? keys.sort((a, b) => parseInt(a, 10) - parseInt(b, 10))
                  : keys;

        sorted.forEach((key, idx) => {
            const p = RAW_PATTERNS[key];
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className =
                'online-exp-trace-card' +
                (idx === 0 ? ' online-exp-trace-card--active' : '') +
                (variant === 'letter' || variant === 'number' ? ' online-exp-trace-card--glyph' : '');
            btn.dataset.pattern = key;
            btn.setAttribute('role', 'option');

            const canvas = document.createElement('canvas');
            canvas.className = 'online-exp-trace-card__preview';
            canvas.dataset.preview = key;
            canvas.width = 80;
            canvas.height = 56;
            canvas.setAttribute('aria-hidden', 'true');

            const title = document.createElement('span');
            title.className = 'online-exp-trace-card__title';
            title.textContent = p.display || p.name;

            const badge = document.createElement('span');
            badge.className = 'online-exp-trace-card__badge';
            badge.textContent = p.level || 'Kolay';

            btn.append(canvas, title, badge);

            if ((variant === 'letter' || variant === 'number') && p.display) {
                const glyph = document.createElement('span');
                glyph.className = 'online-exp-trace-card__glyph';
                glyph.textContent = p.display;
                glyph.setAttribute('aria-hidden', 'true');
                btn.appendChild(glyph);
            }

            el.patterns.appendChild(btn);
        });
    }

    function setupCanvas() {
        if (!el.canvas) return;
        dpr = Math.min(window.devicePixelRatio || 1, 2);
        el.canvas.width = LOGICAL_W * dpr;
        el.canvas.height = LOGICAL_H * dpr;
        el.canvas.style.width = '100%';
        el.canvas.style.height = 'auto';
        ctx = el.canvas.getContext('2d');
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }

    function buildSamples(segments) {
        const out = [];
        const step = 0.025;
        segments.forEach((seg) => {
            for (let i = 0; i < seg.length - 1; i++) {
                const a = seg[i];
                const b = seg[i + 1];
                const dx = b[0] - a[0];
                const dy = b[1] - a[1];
                const dist = Math.sqrt(dx * dx + dy * dy);
                if (dist < 0.001) continue;
                const steps = Math.max(2, Math.ceil(dist / step));
                for (let s = 0; s <= steps; s++) {
                    const t = s / steps;
                    out.push({
                        x: a[0] + dx * t,
                        y: a[1] + dy * t,
                        hit: false,
                    });
                }
            }
        });
        return out;
    }

    function findNextUnhit() {
        for (let i = 0; i < samples.length; i++) {
            if (!samples[i].hit) return i;
        }
        return samples.length - 1;
    }

    function selectPattern(key) {
        if (!patternsNormalized[key]) return;
        currentPattern = key;
        samples = buildSamples(patternsNormalized[key]);
        hitCount = 0;
        nextHintIndex = 0;
        strokeTrail = [];
        completed = false;
        if (el.celebrate) el.celebrate.hidden = true;

        el.patterns.querySelectorAll('.online-exp-trace-card').forEach((b) => {
            b.classList.toggle('online-exp-trace-card--active', b.dataset.pattern === key);
        });
        scheduleRedraw();
        updateProgressUI();
    }

    function scheduleRedraw() {
        if (rafPending) return;
        rafPending = true;
        requestAnimationFrame(() => {
            rafPending = false;
            redraw();
        });
    }

    function strokeSegments(pts, styleFn) {
        pts.forEach((seg) => {
            if (seg.length < 2) return;
            styleFn();
            ctx.beginPath();
            ctx.moveTo(seg[0][0], seg[0][1]);
            for (let i = 1; i < seg.length; i++) ctx.lineTo(seg[i][0], seg[i][1]);
            ctx.stroke();
        });
    }

    function redraw() {
        if (!ctx) return;
        const w = LOGICAL_W;
        const h = LOGICAL_H;
        const segs = patternsNormalized[currentPattern];
        const flat = segs.flat();

        ctx.clearRect(0, 0, w, h);

        const bg = ctx.createLinearGradient(0, 0, w, h);
        bg.addColorStop(0, config.bgFrom || '#fffefb');
        bg.addColorStop(1, config.bgTo || '#f5f3ff');
        ctx.fillStyle = bg;
        ctx.fillRect(0, 0, w, h);

        ctx.save();
        ctx.strokeStyle = config.gridColor || 'rgba(167, 139, 250, 0.12)';
        ctx.lineWidth = 1;
        for (let x = 24; x < w; x += 28) {
            ctx.beginPath();
            ctx.moveTo(x, 0);
            ctx.lineTo(x, h);
            ctx.stroke();
        }
        for (let y = 24; y < h; y += 28) {
            ctx.beginPath();
            ctx.moveTo(0, y);
            ctx.lineTo(w, y);
            ctx.stroke();
        }
        ctx.restore();

        ctx.save();
        ctx.strokeStyle = 'rgba(148, 163, 184, 0.35)';
        ctx.lineWidth = 20;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.setLineDash([]);
        strokeSegments(segs, () => {});
        ctx.restore();

        ctx.save();
        ctx.strokeStyle = '#94a3b8';
        ctx.lineWidth = 5;
        ctx.setLineDash([10, 12]);
        ctx.lineDashOffset = -Date.now() / 40;
        strokeSegments(segs, () => {});
        ctx.restore();

        if (stepIndex >= 3 && !completed) {
            nextHintIndex = findNextUnhit();
            const hint = samples[nextHintIndex];
            if (hint && !hint.hit) {
                const pulse = 0.6 + Math.sin(Date.now() / 200) * 0.25;
                ctx.beginPath();
                ctx.arc(hint.x, hint.y, 10 * pulse, 0, Math.PI * 2);
                ctx.fillStyle = config.hintGlow || 'rgba(124, 58, 237, 0.25)';
                ctx.fill();
                ctx.beginPath();
                ctx.arc(hint.x, hint.y, 5, 0, Math.PI * 2);
                ctx.fillStyle = config.hintColor || '#7c3aed';
                ctx.fill();
            }
        }

        const start = flat[0];
        const end = flat[flat.length - 1];
        if (start) drawMarker(start[0], start[1], '#22c55e', 'BAŞLA');
        if (end && (completed || hitCount > samples.length * 0.5)) {
            drawMarker(end[0], end[1], config.endColor || '#8b5cf6', 'BİTİŞ');
        }

        if (strokeTrail.length > 1) {
            ctx.save();
            ctx.strokeStyle = 'rgba(34, 197, 94, 0.35)';
            ctx.lineWidth = brushSize * 0.9;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
            ctx.beginPath();
            ctx.moveTo(strokeTrail[0].x, strokeTrail[0].y);
            for (let i = 1; i < strokeTrail.length; i++) ctx.lineTo(strokeTrail[i].x, strokeTrail[i].y);
            ctx.stroke();
            ctx.restore();
        }

        const grad = ctx.createLinearGradient(0, 0, w, 0);
        grad.addColorStop(0, config.strokeFrom || '#10b981');
        grad.addColorStop(0.5, config.strokeMid || '#14b8a6');
        grad.addColorStop(1, config.strokeTo || '#8b5cf6');
        ctx.strokeStyle = grad;
        ctx.lineWidth = brushSize;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();
        let started = false;
        samples.forEach((s) => {
            if (s.hit) {
                if (!started) {
                    ctx.moveTo(s.x, s.y);
                    started = true;
                } else ctx.lineTo(s.x, s.y);
            }
        });
        if (started) ctx.stroke();

        if (stepIndex >= 3 && drawing && strokeTrail.length) {
            const last = strokeTrail[strokeTrail.length - 1];
            ctx.beginPath();
            ctx.arc(last.x, last.y, brushSize / 2 + 2, 0, Math.PI * 2);
            ctx.fillStyle = config.hintGlow || 'rgba(124, 58, 237, 0.2)';
            ctx.fill();
            ctx.beginPath();
            ctx.arc(last.x, last.y, brushSize / 2 - 2, 0, Math.PI * 2);
            ctx.fillStyle = config.hintColor || '#7c3aed';
            ctx.fill();
        }
    }

    function startDashAnimation() {
        if (dashAnimActive) return;
        dashAnimActive = true;
        function loop() {
            if (stepIndex !== 3 || completed) {
                dashAnimActive = false;
                return;
            }
            redraw();
            requestAnimationFrame(loop);
        }
        loop();
    }

    function stopDashAnimation() {
        dashAnimActive = false;
    }

    function drawMarker(x, y, color, label) {
        ctx.beginPath();
        ctx.arc(x, y, 11, 0, Math.PI * 2);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = '#fff';
        ctx.lineWidth = 2.5;
        ctx.stroke();
        ctx.font = 'bold 9px system-ui, sans-serif';
        ctx.fillStyle = '#475569';
        ctx.textAlign = 'center';
        ctx.fillText(label, x, y - 18);
    }

    function drawPreview(canvas, key) {
        const pctx = canvas.getContext('2d');
        const pw = canvas.width;
        const ph = canvas.height;
        const segs = patternSegments(key).map((seg) => normalizePoints(seg, pw, ph, 6));
        pctx.fillStyle = config.previewBg || '#faf5ff';
        pctx.fillRect(0, 0, pw, ph);
        pctx.strokeStyle = config.previewStroke || '#a78bfa';
        pctx.lineWidth = 2;
        pctx.lineCap = 'round';
        pctx.lineJoin = 'round';
        segs.forEach((seg) => {
            if (seg.length < 2) return;
            pctx.beginPath();
            pctx.moveTo(seg[0][0], seg[0][1]);
            for (let i = 1; i < seg.length; i++) pctx.lineTo(seg[i][0], seg[i][1]);
            pctx.stroke();
        });
    }

    function drawAllPreviews() {
        document.querySelectorAll('.online-exp-trace-card__preview').forEach((c) => {
            drawPreview(c, c.dataset.preview);
        });
    }

    function pointerPos(e) {
        const rect = el.canvas.getBoundingClientRect();
        const scaleX = LOGICAL_W / rect.width;
        const scaleY = LOGICAL_H / rect.height;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY,
        };
    }

    function onPointerDown(e) {
        if (stepIndex !== 3 || completed) return;
        drawing = true;
        strokeTrail = [];
        onPointerMove(e);
    }

    function onPointerMove(e) {
        if (!drawing || stepIndex !== 3 || completed) return;
        e.preventDefault();
        const p = pointerPos(e);
        strokeTrail.push(p);
        if (strokeTrail.length > 40) strokeTrail.shift();

        const thresh = brushRadius;
        const threshSq = thresh * thresh;
        let any = false;

        samples.forEach((s) => {
            if (s.hit) return;
            const dx = s.x - p.x;
            const dy = s.y - p.y;
            if (dx * dx + dy * dy < threshSq) {
                s.hit = true;
                hitCount++;
                any = true;
                spawnSparkle(s.x, s.y);
            }
        });

        if (any) {
            scheduleRedraw();
            updateProgressUI();
            if (hitCount / samples.length >= TARGET_PCT && !completed) completeTrace();
        } else {
            scheduleRedraw();
        }
    }

    function spawnSparkle(x, y) {
        if (!el.canvasWrap) return;
        const dot = document.createElement('span');
        dot.className = 'online-exp-trace-sparkle';
        const rect = el.canvas.getBoundingClientRect();
        const wrap = el.canvasWrap.getBoundingClientRect();
        dot.style.left = rect.left - wrap.left + (x / LOGICAL_W) * rect.width + 'px';
        dot.style.top = rect.top - wrap.top + (y / LOGICAL_H) * rect.height + 'px';
        el.canvasWrap.appendChild(dot);
        setTimeout(() => dot.remove(), 600);
    }

    function completeTrace() {
        completed = true;
        drawing = false;
        if (el.celebrate) {
            el.celebrate.hidden = false;
            el.celebrate.setAttribute('aria-hidden', 'false');
        }
        flashHint(config.completeHint || 'Muhteşem! Çizgi çalışması tamam 🎉');
        stepIndex = TOTAL_STEPS;
        renderStep();
        scheduleRedraw();
    }

    function updateProgressUI() {
        const pct = samples.length ? Math.round((hitCount / samples.length) * 100) : 0;
        const target = Math.round(TARGET_PCT * 100);
        if (el.progress) {
            el.progress.innerHTML = completed
                ? '<span class="online-exp-trace-progress__done">✓ Tamamlandı — aferin!</span>'
                : 'İlerleme: <strong>%' +
                  pct +
                  '</strong> · hedef %' +
                  target +
                  ' <span class="online-exp-trace-progress__dots" aria-hidden="true"><span class="online-exp-trace-progress__dot online-exp-trace-progress__dot--start"></span> başla <span class="online-exp-trace-progress__dot online-exp-trace-progress__dot--end"></span> bitir</span>';
        }
        if (el.ringFill) {
            const circ = 2 * Math.PI * 18;
            el.ringFill.style.strokeDasharray = circ;
            el.ringFill.style.strokeDashoffset = circ - (circ * pct) / 100;
        }
        if (el.ringPct) el.ringPct.textContent = pct + '%';
    }

    function bindEvents() {
        el.btnPrev.addEventListener('click', () => {
            if (stepIndex > 0 && !completed) {
                stepIndex--;
                if (el.celebrate) el.celebrate.hidden = true;
                renderStep();
            }
        });
        el.btnNext.addEventListener('click', () => {
            if (stepIndex === 3 && !completed) {
                flashHint('Önce çizgiyi tamamla veya %' + Math.round(TARGET_PCT * 100) + ' ulaş.');
                return;
            }
            if (stepIndex < TOTAL_STEPS - 1) {
                stepIndex++;
                renderStep();
            }
        });
        if (el.patterns) {
            el.patterns.addEventListener('click', (e) => {
                const card = e.target.closest('.online-exp-trace-card');
                if (!card) return;
                selectPattern(card.dataset.pattern);
                if (stepIndex >= 1) {
                    const p = RAW_PATTERNS[card.dataset.pattern];
                    flashHint((p?.display || p?.name) + ' seçildi.');
                }
            });
        }
        document.querySelectorAll('.online-exp-trace-brush').forEach((b) => {
            b.addEventListener('click', () => {
                brushRadius = parseInt(b.dataset.size, 10);
                brushSize = brushRadius + 8;
                document.querySelectorAll('.online-exp-trace-brush').forEach((x) => {
                    x.classList.toggle('online-exp-trace-brush--active', x === b);
                });
            });
        });
        if (el.startBtn) {
            el.startBtn.addEventListener('click', () => {
                if (stepIndex === 2) {
                    stepIndex = 3;
                    renderStep();
                    flashHint('Yeşil BAŞLA noktasından başla!');
                }
            });
        }
        if (el.canvas) {
            el.canvas.addEventListener('mousedown', onPointerDown);
            el.canvas.addEventListener('mouseup', () => {
                drawing = false;
            });
            el.canvas.addEventListener('mouseleave', () => {
                drawing = false;
            });
            el.canvas.addEventListener('mousemove', onPointerMove);
            el.canvas.addEventListener('touchstart', onPointerDown, { passive: false });
            el.canvas.addEventListener('touchend', () => {
                drawing = false;
            });
            el.canvas.addEventListener('touchmove', onPointerMove, { passive: false });
        }
        if (el.clearBtn) el.clearBtn.addEventListener('click', clearTrace);
        el.btnRetry.addEventListener('click', resetAll);
        el.btnScreenshot.addEventListener('click', downloadScreenshot);
        window.addEventListener('resize', () => {
            setupCanvas();
            scheduleRedraw();
        });
    }

    function clearTrace() {
        samples.forEach((s) => {
            s.hit = false;
        });
        hitCount = 0;
        strokeTrail = [];
        completed = false;
        if (el.celebrate) el.celebrate.hidden = true;
        scheduleRedraw();
        updateProgressUI();
        flashHint('Sayfa temizlendi — yeniden çiz.');
    }

    function renderStep() {
        const isResult = stepIndex >= TOTAL_STEPS;
        const step = isResult ? null : steps[stepIndex];
        const progress = isResult ? 100 : Math.min(100, Math.round((stepIndex / (TOTAL_STEPS - 1)) * 100));

        el.progressBar.style.width = progress + '%';
        el.badge.textContent = isResult ? 'Tamamlandı!' : 'Adım ' + (stepIndex + 1) + ' / ' + TOTAL_STEPS;

        if (isResult) {
            el.title.textContent = resultCopy.title;
            el.text.textContent = resultCopy.text;
            el.hint.textContent = resultCopy.hint;
            el.sideBody.innerHTML =
                '<div class="online-exp-result">' +
                '<p class="font-semibold text-violet-800">Boya Etkinlik</p>' +
                '<p class="mt-2 text-xs text-slate-600">Ücretsiz boyama ve çizgi çalışması sayfalarımızda aynı beceriyi gerçek kalemle de geliştirebilirsin.</p>' +
                '<a href="/kategoriler" class="mt-3 inline-flex text-xs font-semibold text-violet-700 hover:text-violet-900">Boyama sayfalarına git →</a></div>';
        } else if (step) {
            el.title.textContent = step.title;
            el.text.textContent = step.text;
            el.hint.textContent = step.hint;
            renderChecklist(step.checklist || []);
            el.sideBody.innerHTML = step.sideHtml || '';
        }

        el.btnPrev.disabled = stepIndex === 0;
        el.btnNext.hidden = stepIndex === 2 || stepIndex === 3 || isResult;
        if (el.startBtn) el.startBtn.hidden = stepIndex !== 2;
        if (el.toolbar) el.toolbar.hidden = stepIndex < 3;
        if (el.patterns) el.patterns.hidden = stepIndex < 1;
        if (el.workspace) el.workspace.classList.toggle('online-exp-trace-workspace--live', stepIndex >= 3);
        if (el.canvas) {
            el.canvas.style.pointerEvents = stepIndex === 3 && !completed ? 'auto' : 'none';
            el.canvas.classList.toggle('online-exp-trace-canvas--active', stepIndex === 3);
        }
        if (el.clearBtn) el.clearBtn.hidden = stepIndex !== 3;
        el.sideActions.hidden = !isResult;
        if (stepIndex === 3 && !completed) startDashAnimation();
        else stopDashAnimation();
        scheduleRedraw();
        updateProgressUI();
    }

    function renderChecklist(items) {
        el.checklist.innerHTML = '';
        el.checklist.hidden = !items.length;
        items.forEach((t) => {
            const li = document.createElement('li');
            li.textContent = t;
            el.checklist.appendChild(li);
        });
    }

    function resetAll() {
        stepIndex = 0;
        completed = false;
        drawing = false;
        if (el.celebrate) el.celebrate.hidden = true;
        selectPattern(resetPattern);
        renderStep();
    }

    function downloadScreenshot() {
        if (!el.canvas) return;
        el.canvas.toBlob((blob) => {
            if (!blob) return;
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = downloadPrefix + '-' + currentPattern + '.png';
            a.click();
            URL.revokeObjectURL(a.href);
        }, 'image/png');
    }

    function flashHint(msg) {
        el.hint.textContent = msg;
        el.hint.classList.add('online-exp-hint--flash');
        setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1400);
    }
}

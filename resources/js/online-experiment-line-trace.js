/**
 * Çizgi tamamlama — çizgi çalışması
 */
(function () {
    const app = document.getElementById('online-exp-app');
    if (!app) return;

    const TOTAL_STEPS = 3;
    const PATTERNS = {
        ev: {
            name: 'Ev',
            points: [
                [60, 240],
                [60, 100],
                [200, 100],
                [200, 240],
                [280, 240],
            ],
        },
        kelebek: {
            name: 'Kelebek',
            points: [
                [240, 80],
                [200, 160],
                [120, 200],
                [240, 240],
                [360, 200],
                [280, 160],
                [240, 80],
            ],
        },
        dalga: {
            name: 'Dalga',
            points: (function () {
                const pts = [];
                for (let x = 40; x <= 440; x += 20) {
                    pts.push([x, 150 + Math.sin(x / 35) * 55]);
                }
                return pts;
            })(),
        },
    };

    const steps = [
        {
            title: 'Çizgi tamamlama',
            text: 'Boyama sayfalarındaki çizgi çalışmaları gibi bir desenin üzerinden geçerek çizimi tamamlarsın. El-göz koordinasyonunu güçlendirir.',
            hint: 'Okul öncesi ve ilkokul için uygundur.',
            checklist: ['Desen seç', 'Çizgiyi takip et'],
            sideHtml: '<p class="text-xs text-slate-600">Fare veya parmağınla çizginin üzerinden yavaşça geç. %85 tamamlayınca başarı!</p>',
        },
        {
            title: 'Desenini seç',
            text: 'Ev, kelebek veya dalga desenlerinden birini seç. Sonra çizim alanında gri çizgiyi takip et.',
            hint: 'Üstteki butonlardan birine tıkla.',
            checklist: ['Bir desen seçildi'],
            sideHtml: '',
        },
        {
            title: 'Çizgiyi tamamla',
            text: 'Gri çizginin üzerinden geç; yeşil iz bırakırsın. İlerleme çubuğu dolunca tebrikler!',
            hint: 'Acele etme, çizgiye yakın kal.',
            checklist: ['Çizgi %85+ tamamlandı'],
            sideHtml: '',
        },
    ];

    let stepIndex = 0;
    let currentPattern = 'ev';
    let samples = [];
    let hitCount = 0;
    let drawing = false;
    let completed = false;

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
        progress: document.getElementById('exp-trace-progress'),
        patterns: document.getElementById('exp-trace-patterns'),
        clearBtn: document.getElementById('exp-trace-clear'),
    };

    const ctx = el.canvas ? el.canvas.getContext('2d') : null;

    bindEvents();
    selectPattern('ev');
    renderStep();

    function buildSamples(points) {
        const out = [];
        for (let i = 0; i < points.length - 1; i++) {
            const a = points[i];
            const b = points[i + 1];
            for (let t = 0; t <= 1; t += 0.05) {
                out.push({
                    x: a[0] + (b[0] - a[0]) * t,
                    y: a[1] + (b[1] - a[1]) * t,
                    hit: false,
                });
            }
        }
        return out;
    }

    function selectPattern(key) {
        currentPattern = key;
        samples = buildSamples(PATTERNS[key].points);
        hitCount = 0;
        completed = false;
        el.patterns.querySelectorAll('.online-exp-trace-pattern-btn').forEach((b) => {
            b.classList.toggle('online-exp-trace-pattern-btn--active', b.dataset.pattern === key);
        });
        redraw();
        updateProgressText();
    }

    function redraw() {
        if (!ctx || !el.canvas) return;
        const w = el.canvas.width;
        const h = el.canvas.height;
        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = '#faf5ff';
        ctx.fillRect(0, 0, w, h);

        ctx.strokeStyle = '#cbd5e1';
        ctx.lineWidth = 14;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';
        ctx.beginPath();
        const pts = PATTERNS[currentPattern].points;
        ctx.moveTo(pts[0][0], pts[0][1]);
        for (let i = 1; i < pts.length; i++) ctx.lineTo(pts[i][0], pts[i][1]);
        ctx.stroke();

        ctx.strokeStyle = '#22c55e';
        ctx.lineWidth = 6;
        ctx.lineCap = 'round';
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
    }

    function pointerPos(e) {
        const rect = el.canvas.getBoundingClientRect();
        const scaleX = el.canvas.width / rect.width;
        const scaleY = el.canvas.height / rect.height;
        const clientX = e.touches ? e.touches[0].clientX : e.clientX;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY,
        };
    }

    function onPointerMove(e) {
        if (!drawing || stepIndex !== 2 || completed) return;
        e.preventDefault();
        const p = pointerPos(e);
        let any = false;
        samples.forEach((s) => {
            if (s.hit) return;
            const dx = s.x - p.x;
            const dy = s.y - p.y;
            if (dx * dx + dy * dy < 22 * 22) {
                s.hit = true;
                hitCount++;
                any = true;
            }
        });
        if (any) {
            redraw();
            updateProgressText();
            const pct = hitCount / samples.length;
            if (pct >= 0.85 && !completed) {
                completed = true;
                flashHint('Harika! Çizgi tamamlandı 🎉');
                stepIndex = TOTAL_STEPS;
                renderStep();
            }
        }
    }

    function updateProgressText() {
        const pct = samples.length ? Math.round((hitCount / samples.length) * 100) : 0;
        el.progress.textContent = completed
            ? 'Tamamlandı!'
            : 'İlerleme: %' + pct + ' (hedef %85)';
    }

    function bindEvents() {
        el.btnPrev.addEventListener('click', () => {
            if (stepIndex > 0 && !completed) {
                stepIndex--;
                renderStep();
            }
        });
        el.btnNext.addEventListener('click', () => {
            if (stepIndex === 1) {
                stepIndex = 2;
                renderStep();
                return;
            }
            if (stepIndex < TOTAL_STEPS - 1 && !completed) {
                stepIndex++;
                renderStep();
            }
        });
        el.patterns.querySelectorAll('.online-exp-trace-pattern-btn').forEach((b) => {
            b.addEventListener('click', () => {
                selectPattern(b.dataset.pattern);
                if (stepIndex >= 1) flashHint(PATTERNS[b.dataset.pattern].name + ' deseni seçildi.');
            });
        });
        if (el.canvas) {
            el.canvas.addEventListener('mousedown', () => {
                if (stepIndex === 2) drawing = true;
            });
            el.canvas.addEventListener('mouseup', () => {
                drawing = false;
            });
            el.canvas.addEventListener('mouseleave', () => {
                drawing = false;
            });
            el.canvas.addEventListener('mousemove', onPointerMove);
            el.canvas.addEventListener(
                'touchstart',
                (e) => {
                    if (stepIndex === 2) {
                        drawing = true;
                        onPointerMove(e);
                    }
                },
                { passive: false }
            );
            el.canvas.addEventListener('touchend', () => {
                drawing = false;
            });
            el.canvas.addEventListener('touchmove', onPointerMove, { passive: false });
        }
        if (el.clearBtn) {
            el.clearBtn.addEventListener('click', () => {
                samples.forEach((s) => {
                    s.hit = false;
                });
                hitCount = 0;
                completed = false;
                redraw();
                updateProgressText();
            });
        }
        el.btnRetry.addEventListener('click', resetAll);
        el.btnScreenshot.addEventListener('click', downloadScreenshot);
    }

    function renderStep() {
        const isResult = stepIndex >= TOTAL_STEPS;
        const step = isResult ? null : steps[stepIndex];
        const progress = isResult ? 100 : Math.min(100, Math.round((stepIndex / (TOTAL_STEPS - 1)) * 100));

        el.progressBar.style.width = progress + '%';
        el.badge.textContent = isResult ? 'Tamamlandı!' : 'Adım ' + (stepIndex + 1) + ' / ' + TOTAL_STEPS;

        if (isResult) {
            el.title.textContent = 'Çizgi tamamlandı!';
            el.text.textContent = 'Çizgi çalışması başarıyla bitti. Boyama sayfalarında da aynı dikkati göster — çizgilerin üzerinden geçmek boyayı güzelleştirir.';
            el.hint.textContent = 'Başka desen dene veya sonucu kaydet.';
            el.sideBody.innerHTML =
                '<div class="online-exp-result"><p class="font-semibold text-violet-800">Boya Etkinlik ipucu</p>' +
                '<p class="mt-2 text-xs text-slate-600">Sitemizdeki çizgi çalışması sayfalarında da kalem veya parmakla çizgiyi takip ederek boyama yapabilirsin.</p></div>';
        } else if (step) {
            el.title.textContent = step.title;
            el.text.textContent = step.text;
            el.hint.textContent = step.hint;
            renderChecklist(step.checklist || []);
            el.sideBody.innerHTML = step.sideHtml || '';
        }

        el.btnPrev.disabled = stepIndex === 0;
        el.btnNext.hidden = stepIndex === 2 || isResult;
        el.patterns.hidden = stepIndex < 1;
        if (el.canvas) el.canvas.style.pointerEvents = stepIndex === 2 ? 'auto' : 'none';
        if (el.clearBtn) el.clearBtn.hidden = stepIndex !== 2;
        el.sideActions.hidden = !isResult;
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
        selectPattern('ev');
        renderStep();
    }

    function downloadScreenshot() {
        if (!el.canvas) return;
        el.canvas.toBlob((blob) => {
            if (!blob) return;
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'cizgi-tamamlama-' + currentPattern + '.png';
            a.click();
        });
    }

    function flashHint(msg) {
        el.hint.textContent = msg;
        el.hint.classList.add('online-exp-hint--flash');
        setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1400);
    }
})();

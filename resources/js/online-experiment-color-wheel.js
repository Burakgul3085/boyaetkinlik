/**
 * Renk Çarkı — ana ve ara renkler
 */
(function () {
    const app = document.getElementById('online-exp-app');
    if (!app) return;

    const TOTAL_STEPS = 4;
    const SEGMENTS = [
        { id: 'red', label: 'Kırmızı', fill: '#ef4444', kind: 'primary' },
        { id: 'orange', label: 'Turuncu', fill: '#f97316', kind: 'secondary', mix: 'Kırmızı + Sarı' },
        { id: 'yellow', label: 'Sarı', fill: '#eab308', kind: 'primary' },
        { id: 'green', label: 'Yeşil', fill: '#22c55e', kind: 'secondary', mix: 'Sarı + Mavi' },
        { id: 'blue', label: 'Mavi', fill: '#3b82f6', kind: 'primary' },
        { id: 'purple', label: 'Mor', fill: '#a855f7', kind: 'secondary', mix: 'Mavi + Kırmızı' },
    ];

    const steps = [
        {
            title: 'Renk çarkı nedir?',
            text: 'Boyama kitaplarında üç ana renk vardır: kırmızı, sarı ve mavi. İkisi birleşince ara renkler oluşur.',
            hint: 'Okul öncesi ve ilkokul için ideal.',
            checklist: ['Ana renkleri seç', 'Ara renkleri aç'],
            sideHtml: '<p class="text-xs text-slate-600">Ana renkler karıştırılamaz tonlardır; ara renkler bunların karışımıdır.</p>',
        },
        {
            title: 'Ana renkleri seç',
            text: 'Alttaki Kırmızı, Sarı ve Mavi butonlarına sırayla bas. Çarkta ilgili dilimler parlayacak.',
            hint: 'Üç ana rengi de seç.',
            checklist: ['Kırmızı, sarı, mavi seçildi'],
            sideHtml: '',
        },
        {
            title: 'Ara renkleri keşfet',
            text: '«Ara renkleri göster» butonuna bas. Turuncu, yeşil ve mor dilimler belirecek.',
            hint: 'Her ara rengin hangi ana renklerden oluştuğunu oku.',
            checklist: ['Ara renkler göründü'],
            sideHtml: '',
        },
        {
            title: 'Özet',
            text: 'Turuncu = kırmızı + sarı, Yeşil = sarı + mavi, Mor = mavi + kırmızı. Boyama yaparken bu çarkı aklında tut!',
            hint: 'Baştan dene veya sonucu kaydet.',
            checklist: [],
            sideHtml: '',
        },
    ];

    let stepIndex = 0;
    const primariesDone = { red: false, yellow: false, blue: false };
    let secondariesVisible = false;

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
        wheelG: document.getElementById('exp-wheel-segments'),
        centerText: document.getElementById('exp-wheel-center-text'),
        controls: document.getElementById('exp-wheel-controls'),
        reveal: document.getElementById('exp-wheel-reveal'),
    };

    buildWheel();
    bindEvents();
    renderStep();

    function wedgePath(cx, cy, r, i) {
        const a1 = ((i * 60 - 90) * Math.PI) / 180;
        const a2 = (((i + 1) * 60 - 90) * Math.PI) / 180;
        const x1 = cx + r * Math.cos(a1);
        const y1 = cy + r * Math.sin(a1);
        const x2 = cx + r * Math.cos(a2);
        const y2 = cy + r * Math.sin(a2);
        return 'M ' + cx + ' ' + cy + ' L ' + x1 + ' ' + y1 + ' A ' + r + ' ' + r + ' 0 0 1 ' + x2 + ' ' + y2 + ' Z';
    }

    function buildWheel() {
        if (!el.wheelG) return;
        el.wheelG.innerHTML = '';
        SEGMENTS.forEach((seg, i) => {
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', wedgePath(160, 160, 140, i));
            path.setAttribute('fill', seg.kind === 'secondary' ? '#e2e8f0' : seg.fill);
            path.setAttribute('stroke', '#fff');
            path.setAttribute('stroke-width', '3');
            path.setAttribute('data-id', seg.id);
            path.setAttribute('data-kind', seg.kind);
            path.classList.add('online-exp-wheel-seg');
            if (seg.kind === 'secondary') path.classList.add('online-exp-wheel-seg--muted');
            path.addEventListener('click', () => onSegClick(seg));
            el.wheelG.appendChild(path);
        });
    }

    function onSegClick(seg) {
        if (seg.kind === 'primary' && stepIndex === 1) {
            primariesDone[seg.id] = true;
            highlightPrimary(seg.id);
            checkPrimaries();
        }
        if (seg.kind === 'secondary' && secondariesVisible) {
            el.centerText.textContent = seg.mix || seg.label;
            flashHint(seg.label + ': ' + (seg.mix || ''));
        }
    }

    function highlightPrimary(id) {
        el.wheelG.querySelectorAll('.online-exp-wheel-seg').forEach((p) => {
            const active = p.getAttribute('data-id') === id;
            p.classList.toggle('online-exp-wheel-seg--lit', active);
        });
        const seg = SEGMENTS.find((s) => s.id === id);
        if (seg) el.centerText.textContent = seg.label;
    }

    function checkPrimaries() {
        if (primariesDone.red && primariesDone.yellow && primariesDone.blue) {
            if (el.reveal) el.reveal.hidden = false;
            flashHint('Harika! Şimdi «Ara renkleri göster»e bas.');
        }
    }

    function revealSecondaries() {
        secondariesVisible = true;
        el.wheelG.querySelectorAll('.online-exp-wheel-seg').forEach((p) => {
            const kind = p.getAttribute('data-kind');
            const id = p.getAttribute('data-id');
            if (kind === 'secondary') {
                const seg = SEGMENTS.find((s) => s.id === id);
                p.setAttribute('fill', seg.fill);
                p.classList.remove('online-exp-wheel-seg--muted');
                p.classList.add('online-exp-wheel-seg--secondary');
            }
        });
        el.centerText.textContent = 'Ara renkler';
        if (stepIndex === 2) flashHint('Dilimlere tıklayarak karışımları oku.');
    }

    function bindEvents() {
        el.btnPrev.addEventListener('click', () => {
            if (stepIndex > 0) {
                stepIndex--;
                renderStep();
            }
        });
        el.btnNext.addEventListener('click', () => {
            if (!canAdvance()) {
                flashHint(blockReason());
                return;
            }
            if (stepIndex < TOTAL_STEPS - 1) {
                stepIndex++;
                if (stepIndex === 3) stepIndex = TOTAL_STEPS;
                renderStep();
            }
        });
        el.controls.querySelectorAll('.online-exp-wheel-btn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const id = btn.dataset.primary;
                if (stepIndex === 1 && id) {
                    primariesDone[id] = true;
                    highlightPrimary(id);
                    btn.classList.add('online-exp-wheel-btn--done');
                    checkPrimaries();
                }
            });
        });
        if (el.reveal) {
            el.reveal.addEventListener('click', () => {
                revealSecondaries();
                if (stepIndex === 2) flashHint('Ara renkler açıldı! Sonraki adıma geçebilirsin.');
            });
        }
        el.btnRetry.addEventListener('click', resetAll);
        el.btnScreenshot.addEventListener('click', downloadScreenshot);
    }

    function canAdvance() {
        if (stepIndex === 1) return primariesDone.red && primariesDone.yellow && primariesDone.blue;
        if (stepIndex === 2) return secondariesVisible;
        return true;
    }

    function blockReason() {
        if (stepIndex === 1) return 'Önce kırmızı, sarı ve mavi butonlarına bas.';
        if (stepIndex === 2) return '«Ara renkleri göster» butonuna bas.';
        return '';
    }

    function renderStep() {
        const isResult = stepIndex >= TOTAL_STEPS;
        const step = isResult ? steps[3] : steps[stepIndex];
        const progress = isResult ? 100 : Math.min(100, Math.round((stepIndex / (TOTAL_STEPS - 1)) * 100));

        el.progressBar.style.width = progress + '%';
        el.badge.textContent = isResult ? 'Tamamlandı!' : 'Adım ' + (stepIndex + 1) + ' / ' + TOTAL_STEPS;
        el.title.textContent = step.title;
        el.text.textContent = step.text;
        el.hint.textContent = step.hint;
        renderChecklist(step.checklist || []);
        el.sideBody.innerHTML = isResult ? buildResultHtml() : step.sideHtml || '';

        el.btnPrev.disabled = stepIndex === 0;
        el.btnNext.hidden = stepIndex === 2 || isResult;
        if (el.reveal) el.reveal.hidden = stepIndex !== 2 || secondariesVisible;
        el.controls.hidden = stepIndex !== 1;
        el.sideActions.hidden = !isResult;

        if (isResult) {
            revealSecondaries();
        }
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

    function buildResultHtml() {
        return (
            '<div class="online-exp-result"><p class="font-semibold text-violet-800">Ana → Ara</p>' +
            '<ul class="mt-2 space-y-1 text-xs text-slate-700">' +
            '<li>🟠 Turuncu = Kırmızı + Sarı</li>' +
            '<li>🟢 Yeşil = Sarı + Mavi</li>' +
            '<li>🟣 Mor = Mavi + Kırmızı</li></ul></div>'
        );
    }

    function resetAll() {
        stepIndex = 0;
        secondariesVisible = false;
        primariesDone.red = primariesDone.yellow = primariesDone.blue = false;
        el.controls.querySelectorAll('.online-exp-wheel-btn').forEach((b) => b.classList.remove('online-exp-wheel-btn--done'));
        buildWheel();
        renderStep();
    }

    function downloadScreenshot() {
        const svg = document.getElementById('exp-wheel-svg');
        if (!svg) return;
        const xml = new XMLSerializer().serializeToString(svg);
        const img = new Image();
        const url = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
        img.onload = () => {
            const c = document.createElement('canvas');
            c.width = 640;
            c.height = 640;
            const ctx = c.getContext('2d');
            ctx.fillStyle = '#faf5ff';
            ctx.fillRect(0, 0, 640, 640);
            ctx.drawImage(img, 0, 0, 640, 640);
            c.toBlob((blob) => {
                if (!blob) return;
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'renk-carki.png';
                a.click();
            });
        };
        img.src = url;
    }

    function flashHint(msg) {
        el.hint.textContent = msg;
        el.hint.classList.add('online-exp-hint--flash');
        setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1400);
    }
})();

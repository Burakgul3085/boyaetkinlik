/**
 * Online Deney — İki Renk Karışım Stüdyosu (boya paleti)
 */
(function () {
    const app = document.getElementById('online-exp-app');
    if (!app) return;

    const TOTAL_STEPS = 4;
    const PALETTE = [
        { hex: '#ef4444', label: 'Kırmızı' },
        { hex: '#f97316', label: 'Turuncu' },
        { hex: '#eab308', label: 'Sarı' },
        { hex: '#22c55e', label: 'Yeşil' },
        { hex: '#06b6d4', label: 'Turkuaz' },
        { hex: '#3b82f6', label: 'Mavi' },
        { hex: '#a855f7', label: 'Mor' },
        { hex: '#ec4899', label: 'Pembe' },
        { hex: '#ffffff', label: 'Beyaz' },
        { hex: '#1e293b', label: 'Siyah' },
    ];

    const CLASSIC_HINTS = [
        { a: '#ef4444', b: '#eab308', result: 'Turuncu tonları' },
        { a: '#eab308', b: '#3b82f6', result: 'Yeşilimsi tonlar' },
        { a: '#3b82f6', b: '#ef4444', result: 'Morumsu tonlar' },
        { a: '#ffffff', b: '#ef4444', result: 'Açık pembe / pastel' },
        { a: '#1e293b', b: '#ffffff', result: 'Gri tonlar' },
    ];

    const steps = [
        {
            title: 'Boya paleti nedir?',
            text: 'Resim yaparken iki rengi karıştırarak yeni tonlar elde edersin. Bu stüdyoda aynı mantığı bilgisayarda, güvenle deneyebilirsin.',
            hint: 'Okul öncesi, ilkokul ve ortaokul için uygundur.',
            checklist: ['İki renk seçeceksin', 'Ortada karışımı göreceksin', 'Boyama çalışmalarına ilham verir'],
            sideHtml:
                '<div class="online-exp-info-box">' +
                '<p class="online-exp-info-box__title">Neden önemli?</p>' +
                '<p class="text-xs leading-relaxed text-slate-600">Çizgi çalışması ve boyamada doğru tonu bulmak için renkleri tanımak gerekir. Bu deney renk gözünü güçlendirir.</p>' +
                '</div>',
        },
        {
            title: 'Birinci rengi seç',
            text: 'Soldaki «1. renk» kutusuna tıkla, alttan bir renk seç. İstediğin rengi özel renkten de seçebilirsin.',
            hint: 'Örneğin kırmızı veya mavi ile başla.',
            checklist: ['1. renk dolu olmalı'],
            sideHtml: '<p class="text-sm text-slate-600">Ana renkler (kırmızı, sarı, mavi) ile başlamak kolaydır.</p>',
        },
        {
            title: 'İkinci rengi seç',
            text: 'Sağdaki «2. renk» kutusuna tıkla ve farklı bir renk seç. İki renk de hazır olunca karıştırabilirsin.',
            hint: 'Farklı renkler daha belirgin sonuç verir.',
            checklist: ['2. renk dolu olmalı'],
            sideHtml: '<p class="text-sm text-slate-600">Aynı rengi iki kez seçersen ton değişmez; farklı renkler dene.</p>',
        },
        {
            title: 'Karıştır!',
            text: '«Karıştır» butonuna bas. Ortadaki kasede iki rengin birleşimini göreceksin — boyama defterinde kullanacağın tona yakın bir sonuç.',
            hint: 'İstediğin kadar yeni kombinasyon dene.',
            checklist: ['Karışım tamamlandı'],
            sideHtml: '',
        },
    ];

    let stepIndex = 0;
    let colorA = null;
    let colorB = null;
    let activeSlot = null;
    let selectedPaletteColor = PALETTE[0].hex;
    let mixResultHex = null;
    let mixDone = false;

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
        paletteBar: document.getElementById('exp-mix-palette-bar'),
        paletteHint: document.getElementById('exp-mix-palette-hint'),
        paletteColors: document.getElementById('exp-mix-palette-colors'),
        colorPicker: document.getElementById('exp-mix-color-picker'),
        btnA: document.getElementById('exp-mix-btn-a'),
        btnB: document.getElementById('exp-mix-btn-b'),
        circleA: document.getElementById('exp-mix-circle-a'),
        circleB: document.getElementById('exp-mix-circle-b'),
        nameA: document.getElementById('exp-mix-name-a'),
        nameB: document.getElementById('exp-mix-name-b'),
        bowl: document.getElementById('exp-mix-bowl'),
        result: document.getElementById('exp-mix-result'),
        swirl: document.getElementById('exp-mix-swirl'),
        resultLabel: document.getElementById('exp-mix-result-label'),
        btnStir: document.getElementById('exp-mix-stir'),
        slotA: document.getElementById('exp-mix-slot-a'),
        slotB: document.getElementById('exp-mix-slot-b'),
    };

    buildPalette();
    bindEvents();
    renderStep();

    function buildPalette() {
        if (!el.paletteColors) return;
        el.paletteColors.innerHTML = '';
        PALETTE.forEach((c) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'online-exp-color-btn';
            b.style.backgroundColor = c.hex;
            b.title = c.label;
            b.dataset.hex = c.hex;
            b.setAttribute('aria-label', c.label);
            if (c.hex === '#ffffff') b.style.border = '2px solid #cbd5e1';
            b.addEventListener('click', () => applyColorToActiveSlot(c.hex, c.label));
            el.paletteColors.appendChild(b);
        });
        if (el.colorPicker) {
            el.colorPicker.addEventListener('input', () => {
                applyColorToActiveSlot(el.colorPicker.value, 'Özel renk');
            });
        }
    }

    function bindEvents() {
        el.btnPrev.addEventListener('click', () => {
            if (stepIndex > 0) {
                stepIndex--;
                mixDone = false;
                renderStep();
            }
        });
        el.btnNext.addEventListener('click', () => {
            if (stepIndex >= TOTAL_STEPS) return;
            if (!canAdvance()) {
                flashHint(blockReason());
                return;
            }
            if (stepIndex < TOTAL_STEPS - 1) {
                stepIndex++;
                renderStep();
            }
        });
        el.btnA.addEventListener('click', () => openSlotPicker('a'));
        el.btnB.addEventListener('click', () => openSlotPicker('b'));
        el.btnStir.addEventListener('click', runMix);
        el.btnRetry.addEventListener('click', resetAll);
        el.btnScreenshot.addEventListener('click', downloadScreenshot);
    }

    function openSlotPicker(slot) {
        if (stepIndex !== 1 && stepIndex !== 2) return;
        activeSlot = slot;
        if (el.paletteBar) el.paletteBar.hidden = false;
        if (el.paletteHint) {
            el.paletteHint.textContent =
                slot === 'a' ? '① Birinci renk — paletten seç' : '② İkinci renk — paletten seç';
        }
        el.slotA.classList.toggle('online-exp-mix-slot--active', slot === 'a');
        el.slotB.classList.toggle('online-exp-mix-slot--active', slot === 'b');
        flashHint(slot === 'a' ? '1. renk için bir ton seç.' : '2. renk için farklı bir ton seç.');
    }

    function applyColorToActiveSlot(hex, label) {
        if (!activeSlot) return;
        if (activeSlot === 'a') {
            colorA = hex;
            paintSlot(el.circleA, el.nameA, hex, label);
            if (stepIndex === 1 && colorA) flashHint('1. renk hazır! Sonraki adıma geçebilirsin.');
        } else {
            colorB = hex;
            paintSlot(el.circleB, el.nameB, hex, label);
            if (stepIndex === 2 && colorB) flashHint('2. renk hazır! Sonraki adıma geç.');
        }
        updateBowlPreview();
    }

    function paintSlot(circle, nameEl, hex, label) {
        if (!circle) return;
        circle.style.backgroundColor = hex;
        circle.classList.add('online-exp-mix-slot__circle--filled');
        if (nameEl) nameEl.textContent = label;
    }

    function canAdvance() {
        if (stepIndex === 1) return !!colorA;
        if (stepIndex === 2) return !!colorB;
        if (stepIndex === 3) return mixDone;
        return true;
    }

    function blockReason() {
        if (stepIndex === 1) return 'Önce 1. rengi seç (soldaki kutuya tıkla).';
        if (stepIndex === 2) return 'Önce 2. rengi seç (sağdaki kutuya tıkla).';
        if (stepIndex === 3) return '«Karıştır» butonuna bas.';
        return '';
    }

    function renderStep() {
        const isResult = stepIndex >= TOTAL_STEPS;
        const step = isResult ? null : steps[stepIndex];
        const progress = isResult ? 100 : Math.min(100, Math.round((stepIndex / (TOTAL_STEPS - 1)) * 100));

        el.progressBar.style.width = progress + '%';
        el.badge.textContent = isResult ? 'Tamamlandı!' : 'Adım ' + (stepIndex + 1) + ' / ' + TOTAL_STEPS;

        if (step) {
            el.title.textContent = step.title;
            el.text.textContent = step.text;
            el.hint.textContent = step.hint;
            renderChecklist(step.checklist || []);
            el.sideBody.innerHTML = step.sideHtml || '';
        }

        el.btnPrev.disabled = stepIndex === 0;
        el.btnNext.hidden = stepIndex === 3 || isResult;
        el.btnStir.hidden = stepIndex !== 3;
        el.paletteBar.hidden = stepIndex !== 1 && stepIndex !== 2;
        el.sideActions.hidden = !isResult;

        el.slotA.classList.toggle('online-exp-mix-slot--target', stepIndex === 1);
        el.slotB.classList.toggle('online-exp-mix-slot--target', stepIndex === 2);
        el.slotA.classList.remove('online-exp-mix-slot--active');
        el.slotB.classList.remove('online-exp-mix-slot--active');

        if (stepIndex === 1) activeSlot = 'a';
        if (stepIndex === 2) activeSlot = 'b';

        if (isResult) {
            el.title.textContent = 'Harika karışım!';
            el.text.textContent = 'Yeni rengini keşfettin. Boyama sayfalarında bu tonlara benzer renkler kullanabilirsin.';
            el.hint.textContent = 'Baştan dene veya sonucu kaydet.';
            el.sideBody.innerHTML = buildResultSideHtml();
        }

        updateBowlPreview();
    }

    function renderChecklist(items) {
        el.checklist.innerHTML = '';
        if (!items.length) {
            el.checklist.hidden = true;
            return;
        }
        el.checklist.hidden = false;
        items.forEach((item) => {
            const li = document.createElement('li');
            li.textContent = item;
            el.checklist.appendChild(li);
        });
    }

    function updateBowlPreview() {
        if (mixDone && mixResultHex) {
            el.result.style.background = mixResultHex;
            el.resultLabel.textContent = describeMix(mixResultHex);
            return;
        }
        if (colorA && colorB) {
            const preview = blendHex(colorA, colorB);
            el.result.style.background =
                'linear-gradient(135deg, ' + colorA + ' 0%, ' + colorB + ' 50%, ' + preview + ' 100%)';
            el.resultLabel.textContent = 'Karıştırmaya hazır';
        } else if (colorA) {
            el.result.style.background = colorA;
            el.resultLabel.textContent = '2. rengi de seç';
        } else {
            el.result.style.background = 'linear-gradient(180deg, #f5f3ff, #ede9fe)';
            el.resultLabel.textContent = 'Karışım burada görünür';
        }
    }

    function runMix() {
        if (!colorA || !colorB) {
            flashHint('İki renk de seçili olmalı.');
            return;
        }
        mixResultHex = blendHex(colorA, colorB);
        el.btnStir.disabled = true;
        el.bowl.classList.add('online-exp-mix-bowl--stirring');
        if (el.swirl) el.swirl.hidden = false;

        setTimeout(() => {
            el.bowl.classList.remove('online-exp-mix-bowl--stirring');
            if (el.swirl) el.swirl.hidden = true;
            el.result.style.background = mixResultHex;
            el.result.classList.add('online-exp-mix-bowl__inner--done');
            el.resultLabel.textContent = describeMix(mixResultHex);
            mixDone = true;
            el.btnStir.disabled = false;
            flashHint('Karışım hazır! Sonuç sağ panelde.');
            stepIndex = TOTAL_STEPS;
            renderStep();
        }, 1600);
    }

    function buildResultSideHtml() {
        const hint = findClassicHint(colorA, colorB);
        let extra = '';
        if (hint) {
            extra = '<p class="mt-2 text-xs text-violet-700"><strong>İpucu:</strong> ' + hint + '</p>';
        }
        return (
            '<div class="online-exp-result">' +
            '<p class="font-semibold text-violet-800">Senin karışımın</p>' +
            '<div class="mt-2 flex items-center gap-2">' +
            '<span class="h-8 w-8 rounded-full border-2 border-white shadow" style="background:' +
            colorA +
            '"></span>' +
            '<span class="text-slate-400">+</span>' +
            '<span class="h-8 w-8 rounded-full border-2 border-white shadow" style="background:' +
            colorB +
            '"></span>' +
            '<span class="text-slate-400">=</span>' +
            '<span class="h-8 w-8 rounded-full border-2 border-white shadow" style="background:' +
            mixResultHex +
            '"></span>' +
            '</div>' +
            '<p class="mt-2 text-sm text-slate-700">' +
            describeMix(mixResultHex) +
            '</p>' +
            extra +
            '<p class="online-exp-science mt-3">Boyama kitaplarında ve çizgi çalışmalarında bu tonu referans alarak boyayabilirsin.</p>' +
            '</div>'
        );
    }

    function findClassicHint(a, b) {
        for (let i = 0; i < CLASSIC_HINTS.length; i++) {
            const h = CLASSIC_HINTS[i];
            if (colorsClose(a, h.a) && colorsClose(b, h.b)) return h.result;
            if (colorsClose(a, h.b) && colorsClose(b, h.a)) return h.result;
        }
        return null;
    }

    function colorsClose(c1, c2) {
        const A = hexToRgb(c1);
        const B = hexToRgb(c2);
        if (!A || !B) return false;
        return Math.abs(A.r - B.r) < 40 && Math.abs(A.g - B.g) < 40 && Math.abs(A.b - B.b) < 40;
    }

    function describeMix(hex) {
        const rgb = hexToRgb(hex);
        if (!rgb) return 'Yeni renk tonu';
        const { r, g, b } = rgb;
        if (r > 200 && g > 200 && b > 200) return 'Açık / pastel ton';
        if (r < 80 && g < 80 && b < 80) return 'Koyu ton';
        if (r > g && r > b) return g > b ? 'Sıcak turuncu-kırmızı ton' : 'Sıcak kırmızı ton';
        if (g > r && g > b) return 'Yeşilimsi ton';
        if (b > r && b > g) return 'Soğuk mavi-mor ton';
        if (r > 150 && b > 150) return 'Mor / leylak ton';
        if (r > 150 && g > 150) return 'Sarı-yeşil / limon ton';
        return 'Özel karışım tonu';
    }

    function resetAll() {
        stepIndex = 0;
        colorA = null;
        colorB = null;
        mixResultHex = null;
        mixDone = false;
        activeSlot = null;
        [el.circleA, el.circleB].forEach((c) => {
            if (!c) return;
            c.style.backgroundColor = '';
            c.classList.remove('online-exp-mix-slot__circle--filled');
        });
        if (el.nameA) el.nameA.textContent = 'Seç';
        if (el.nameB) el.nameB.textContent = 'Seç';
        el.result.classList.remove('online-exp-mix-bowl__inner--done');
        renderStep();
    }

    function downloadScreenshot() {
        const canvas = document.createElement('canvas');
        canvas.width = 800;
        canvas.height = 420;
        const ctx = canvas.getContext('2d');
        if (!ctx || !mixResultHex) return;

        const grd = ctx.createLinearGradient(0, 0, 800, 420);
        grd.addColorStop(0, '#faf5ff');
        grd.addColorStop(1, '#ede9fe');
        ctx.fillStyle = grd;
        ctx.fillRect(0, 0, 800, 420);

        ctx.fillStyle = '#5b21b6';
        ctx.font = 'bold 22px system-ui, sans-serif';
        ctx.fillText('İki Renk Karışım Stüdyosu', 40, 48);
        ctx.font = '14px system-ui';
        ctx.fillStyle = '#64748b';
        ctx.fillText('Boya Etkinlik — Online Laboratuvar', 40, 72);

        function drawCircle(x, y, r, col) {
            ctx.beginPath();
            ctx.arc(x, y, r, 0, Math.PI * 2);
            ctx.fillStyle = col;
            ctx.fill();
            ctx.strokeStyle = '#e2e8f0';
            ctx.lineWidth = 3;
            ctx.stroke();
        }

        drawCircle(180, 220, 50, colorA);
        drawCircle(400, 220, 70, mixResultHex);
        drawCircle(620, 220, 50, colorB);
        ctx.fillStyle = '#94a3b8';
        ctx.font = 'bold 28px system-ui';
        ctx.fillText('+', 290, 230);
        ctx.fillText('=', 510, 230);

        canvas.toBlob((blob) => {
            if (!blob) return;
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = 'boya-paleti-karisim.png';
            a.click();
            URL.revokeObjectURL(a.href);
        });
    }

    function hexToRgb(hex) {
        const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex || '');
        if (!m) return null;
        return { r: parseInt(m[1], 16), g: parseInt(m[2], 16), b: parseInt(m[3], 16) };
    }

    function blendHex(a, b) {
        const A = hexToRgb(a);
        const B = hexToRgb(b);
        if (!A || !B) return a || b || '#94a3b8';
        return (
            '#' +
            [Math.round((A.r + B.r) / 2), Math.round((A.g + B.g) / 2), Math.round((A.b + B.b) / 2)]
                .map((x) => x.toString(16).padStart(2, '0'))
                .join('')
        );
    }

    function flashHint(msg) {
        el.hint.textContent = msg;
        el.hint.classList.add('online-exp-hint--flash');
        setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1400);
    }
})();

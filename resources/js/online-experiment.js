/**
 * Online Deney Laboratuvarı — Yürüyen renkler (3D model + adım adım rehber)
 */
(function () {
    const app = document.getElementById('online-exp-app');
    if (!app) return;

    const labType = app.dataset.labType || '';
    const modules = {
        yuruyen_renkler: initWalkWater,
    };

    if (typeof modules[labType] === 'function') {
        modules[labType](app);
    }

    function initWalkWater(root) {
        const TOTAL_STEPS = 6;
        const SOURCE_INDEXES = [0, 2, 4, 6];
        const MIX_INDEXES = [1, 3, 5];
        const PALETTE = [
            { hex: '#ef4444', label: 'Kırmızı' },
            { hex: '#f97316', label: 'Turuncu' },
            { hex: '#eab308', label: 'Sarı' },
            { hex: '#22c55e', label: 'Yeşil' },
            { hex: '#14b8a6', label: 'Turkuaz' },
            { hex: '#3b82f6', label: 'Mavi' },
            { hex: '#a855f7', label: 'Mor' },
            { hex: '#ec4899', label: 'Pembe' },
        ];

        const steps = [
            {
                title: 'Bu deney ne?',
                text: 'Yürüyen renkler (gökkuşağı) deneyinde renkli sular, kağıt havlu köprüleriyle yan bardaklara «yürür». Burada aynı olayı 3D animasyonla güvenle izleyeceksin.',
                hint: 'Soldaki adımları sırayla takip et. Hazırsan «Sonraki adım»a bas.',
                checklist: [
                    '7 bardak ve 6 kağıt havlu köprüsü göreceksin',
                    '1, 3, 5, 7 bardaklara renk koyacaksın',
                    'Köprüleri kurup animasyonu izleyeceksin',
                ],
                sideHtml:
                    '<div class="online-exp-info-box">' +
                    '<p class="online-exp-info-box__title">Gerçek deneyde ne lazım?</p>' +
                    '<ul class="online-exp-info-list">' +
                    '<li>7 şeffaf bardak + su</li>' +
                    '<li>Gıda boyası (istediğin renkler)</li>' +
                    '<li>6 adet kağıt havlu şeridi</li>' +
                    '</ul>' +
                    '<p class="online-exp-info-box__note">Ana bardaklar: <strong>1 · 3 · 5 · 7</strong> renkli. Ara bardaklar <strong>2 · 4 · 6</strong> başta boş; renkler köprüden geçince dolar.</p>' +
                    '</div>',
            },
            {
                title: 'Bardakları tanı',
                text: 'Masada 7 bardak var. Tek numaralılar (1, 3, 5, 7) renkli su alır; çift numaralılar (2, 4, 6) başta boş kalır — renkler köprüyle oraya akacak.',
                hint: 'Sahnedeki numaralara bak. Sonraki adımda renkleri sen koyacaksın.',
                checklist: ['1, 3, 5, 7 → renkli su', '2, 4, 6 → boş (sonra dolacak)'],
                sideHtml:
                    '<div class="online-exp-diagram">' +
                    '<p class="font-semibold text-violet-800">Düzen</p>' +
                    '<p class="mt-2 text-xs leading-relaxed text-slate-600">Renkli sular yan yana durunca, aradaki boş bardaklara su emilerek yeni renkler oluşur — tıpkı gökkuşağı gibi.</p>' +
                    '</div>',
            },
            {
                title: 'Renkleri yerleştir',
                text: 'Paletten veya «özel renk»ten seç. Her ana bardağa (1, 3, 5, 7) farklı renk koyabilirsin — ara bardaklarda komşu renkler karışacak.',
                hint: 'Dört ana bardak da dolunca sonraki adıma geç.',
                checklist: ['8 hazır renk veya özel renk', '1, 3, 5 ve 7 dolu olmalı'],
                sideHtml:
                    '<p class="text-sm text-slate-600">Örneğin 1=mavi, 3=sarı, 5=kırmızı, 7=yeşil seçersen ara bardaklarda otomatik karışımlar oluşur.</p>',
            },
            {
                title: 'Kağıt havlu köprüleri',
                text: 'İki bardağın arasındaki şeride tıkla — kağıt havlu köprüsü kurulur. Altı köprünün hepsi yanmalı (turuncu renk).',
                hint: 'Her boşluğa bir köprü: 1↔2, 2↔3, 3↔4, 4↔5, 5↔6, 6↔7',
                checklist: ['6 köprü de kurulu olmalı'],
                sideHtml:
                    '<p class="text-sm text-slate-600"><strong>Emilim (kapiler etki):</strong> Kağıt havlu suyu ve rengi yukarı çeker; bu yüzden renk «yürür» gibi görünür.</p>',
            },
            {
                title: 'Deneyi başlat',
                text: 'Her şey hazır! Büyük butona bas: renkler köprülerden akacak, 2 · 4 · 6 numaralı bardaklarda senin seçtiğin renklerin karışımı oluşacak.',
                hint: 'Animasyon birkaç saniye sürer; gözün masada olsun.',
                checklist: ['Renkler ve köprüler tamam'],
                sideHtml:
                    '<p class="text-sm text-slate-600">Bu bir <strong>bilgisayar modeli</strong>dir. Gerçekte suyun yükselmesi 30 dakika — 2 saat sürebilir; sabırlı ol!</p>',
            },
            {
                title: 'Sonuç ve bilim',
                text: 'Harika! Ara bardaklarda komşu renklerin karışımı oluştu. Gerçek deneyde de seçtiğin renklere göre benzer sonuç görürsün.',
                hint: 'Sağdan yeniden dene veya sonucu PNG olarak indir.',
                checklist: [],
                sideHtml: '',
            },
        ];

        let stepIndex = 0;
        let selectedColor = PALETTE[0].hex;
        const cupColors = Array(7).fill(null);
        const mixFillColors = Array(7).fill(null);
        const bridges = Array(6).fill(false);
        let animating = false;
        let lastMixSummary = [];

        const el = {
            progressBar: document.getElementById('exp-progress-bar'),
            badge: document.getElementById('exp-step-badge'),
            title: document.getElementById('exp-step-title'),
            text: document.getElementById('exp-step-text'),
            checklist: document.getElementById('exp-checklist'),
            btnPrev: document.getElementById('exp-btn-prev'),
            btnNext: document.getElementById('exp-btn-next'),
            palette: document.getElementById('exp-palette'),
            paletteColors: document.getElementById('exp-palette-colors'),
            colorPicker: document.getElementById('exp-color-picker'),
            cupsRow: document.getElementById('exp-cups-row'),
            arena: document.getElementById('exp-3d-arena'),
            world: document.getElementById('exp-3d-world'),
            hint: document.getElementById('exp-stage-hint'),
            btnStart: document.getElementById('exp-btn-start'),
            sideBody: document.getElementById('exp-side-body'),
            sideActions: document.getElementById('exp-side-actions'),
            btnRetry: document.getElementById('exp-btn-retry'),
            btnScreenshot: document.getElementById('exp-btn-screenshot'),
        };

        buildLabRow();
        buildPalette();
        bindEvents();
        renderStep();

        function buildLabRow() {
            el.cupsRow.innerHTML = '';
            for (let i = 0; i < 7; i++) {
                const cup = document.createElement('button');
                cup.type = 'button';
                cup.className = 'online-exp-cup online-exp-cup--3d';
                cup.dataset.index = String(i);
                const isSource = SOURCE_INDEXES.includes(i);
                cup.innerHTML =
                    '<span class="online-exp-cup__num">' +
                    (i + 1) +
                    '</span>' +
                    '<div class="online-exp-cup__body">' +
                    '<span class="online-exp-cup__glass"></span>' +
                    '<span class="online-exp-cup__water"></span>' +
                    '<span class="online-exp-cup__shine"></span>' +
                    '</div>' +
                    '<span class="online-exp-cup__role">' +
                    (isSource ? 'Renkli' : 'Boş') +
                    '</span>';
                cup.addEventListener('click', () => onCupClick(i));
                el.cupsRow.appendChild(cup);

                if (i < 6) {
                    const bridge = document.createElement('button');
                    bridge.type = 'button';
                    bridge.className = 'online-exp-bridge online-exp-bridge--3d';
                    bridge.dataset.index = String(i);
                    bridge.title = 'Köprü ' + (i + 1) + '↔' + (i + 2);
                    bridge.innerHTML =
                        '<span class="online-exp-bridge__strip"></span>' +
                        '<span class="online-exp-bridge__flow"></span>' +
                        '<span class="online-exp-bridge__label">havlu</span>';
                    bridge.addEventListener('click', () => onBridgeClick(i));
                    el.cupsRow.appendChild(bridge);
                }
            }
        }

        function buildPalette() {
            el.paletteColors.innerHTML = '';
            PALETTE.forEach((c) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'online-exp-color-btn';
                b.style.backgroundColor = c.hex;
                b.title = c.label;
                b.setAttribute('aria-label', c.label);
                b.dataset.hex = c.hex;
                b.innerHTML = '<span class="online-exp-color-btn__name">' + c.label + '</span>';
                if (c.hex === selectedColor) b.classList.add('online-exp-color-btn--active');
                b.addEventListener('click', () => selectColor(c.hex));
                el.paletteColors.appendChild(b);
            });

            if (el.colorPicker) {
                el.colorPicker.addEventListener('input', () => {
                    selectColor(el.colorPicker.value);
                });
            }
        }

        function selectColor(hex) {
            selectedColor = hex;
            if (el.colorPicker) el.colorPicker.value = hex;
            el.paletteColors.querySelectorAll('.online-exp-color-btn').forEach((btn) => {
                btn.classList.toggle('online-exp-color-btn--active', btn.dataset.hex === selectedColor);
            });
        }

        function hexToRgb(hex) {
            const m = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex || '');
            if (!m) return null;
            return { r: parseInt(m[1], 16), g: parseInt(m[2], 16), b: parseInt(m[3], 16) };
        }

        function rgbToHex(r, g, b) {
            const clamp = (n) => Math.max(0, Math.min(255, Math.round(n)));
            return (
                '#' +
                [clamp(r), clamp(g), clamp(b)]
                    .map((x) => x.toString(16).padStart(2, '0'))
                    .join('')
            );
        }

        function blendHex(a, b) {
            const A = hexToRgb(a);
            const B = hexToRgb(b);
            if (!A && B) return b;
            if (A && !B) return a;
            if (!A && !B) return '#94a3b8';
            return rgbToHex((A.r + B.r) / 2, (A.g + B.g) / 2, (A.b + B.b) / 2);
        }

        function colorAt(index) {
            return cupColors[index] || mixFillColors[index] || null;
        }

        function computeMixForCup(mixIdx) {
            return blendHex(colorAt(mixIdx - 1), colorAt(mixIdx + 1));
        }

        function computeAllMixes() {
            const out = {};
            MIX_INDEXES.forEach((idx) => {
                out[idx] = computeMixForCup(idx);
            });
            return out;
        }

        function bindEvents() {
            el.btnPrev.addEventListener('click', () => {
                if (stepIndex > 0 && !animating && stepIndex < TOTAL_STEPS) {
                    stepIndex--;
                    renderStep();
                }
            });
            el.btnNext.addEventListener('click', () => {
                if (animating || stepIndex >= TOTAL_STEPS) return;
                if (!canAdvanceFromStep(stepIndex)) {
                    flashHint(blockReason(stepIndex));
                    return;
                }
                if (stepIndex < TOTAL_STEPS - 1) {
                    stepIndex++;
                    renderStep();
                }
            });
            el.btnStart.addEventListener('click', runAnimation);
            el.btnRetry.addEventListener('click', resetAll);
            el.btnScreenshot.addEventListener('click', downloadScreenshot);
        }

        function canAdvanceFromStep(idx) {
            if (idx === 2) return sourcesReady();
            if (idx === 3) return bridgesReady();
            return true;
        }

        function blockReason(idx) {
            if (idx === 2) return 'Önce 1, 3, 5 ve 7 numaralı bardaklara renk koy.';
            if (idx === 3) return 'Altı köprünün hepsini kur (aradaki şeritlere tıkla).';
            return '';
        }

        function onCupClick(index) {
            if (stepIndex !== 2 || animating) return;
            if (!SOURCE_INDEXES.includes(index)) {
                flashHint('Sadece 1, 3, 5 ve 7 numaralı (renkli) bardaklara boya koy.');
                pulseCup(index, 'warn');
                return;
            }
            cupColors[index] = selectedColor;
            updateCupVisuals();
            pulseCup(index, 'ok');
            if (sourcesReady()) {
                flashHint('Harika! Dört bardak da dolu. «Sonraki adım»a geçebilirsin.');
            }
        }

        function onBridgeClick(index) {
            if (stepIndex !== 3 || animating) return;
            bridges[index] = !bridges[index];
            updateBridgeVisuals();
            if (bridgesReady()) {
                flashHint('Tüm köprüler hazır! Sonraki adıma geç.');
            }
        }

        function sourcesReady() {
            return SOURCE_INDEXES.every((i) => cupColors[i] !== null);
        }

        function bridgesReady() {
            return bridges.every(Boolean);
        }

        function renderStep() {
            const isResult = stepIndex >= TOTAL_STEPS;
            const step = isResult ? steps[5] : steps[stepIndex];
            const progress = isResult ? 100 : Math.min(100, Math.round((stepIndex / (TOTAL_STEPS - 1)) * 100));

            el.progressBar.style.width = progress + '%';
            el.badge.textContent = isResult ? 'Tamamlandı!' : 'Adım ' + (stepIndex + 1) + ' / ' + TOTAL_STEPS;
            el.title.textContent = step.title;
            el.text.textContent = step.text;
            el.hint.textContent = step.hint;

            el.btnPrev.disabled = stepIndex === 0 || animating || isResult;
            el.btnNext.hidden = stepIndex === 4 || isResult;
            el.btnNext.disabled = animating;
            el.btnStart.hidden = stepIndex !== 4;
            el.palette.hidden = stepIndex !== 2;
            el.sideActions.hidden = !isResult;

            renderChecklist(step.checklist || []);
            el.sideBody.innerHTML = isResult ? buildResultSideHtml() : step.sideHtml || '';

            el.arena.classList.toggle('online-exp-3d-arena--intro', stepIndex <= 1);
            el.arena.classList.toggle('online-exp-3d-arena--colors', stepIndex === 2);
            el.arena.classList.toggle('online-exp-3d-arena--bridges', stepIndex === 3);
            el.arena.classList.toggle('online-exp-3d-arena--run', stepIndex === 4);

            updateCupVisuals();
            updateBridgeVisuals();
            updateHighlights();
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

        function updateHighlights() {
            el.cupsRow.querySelectorAll('.online-exp-cup').forEach((cup) => {
                const i = parseInt(cup.dataset.index, 10);
                cup.classList.remove('online-exp-cup--target', 'online-exp-cup--dim');
                if (stepIndex === 1) {
                    cup.classList.toggle('online-exp-cup--target', SOURCE_INDEXES.includes(i));
                    cup.classList.toggle('online-exp-cup--dim', !SOURCE_INDEXES.includes(i));
                }
                if (stepIndex === 2 && SOURCE_INDEXES.includes(i) && !cupColors[i]) {
                    cup.classList.add('online-exp-cup--target');
                }
            });
            el.cupsRow.querySelectorAll('.online-exp-bridge').forEach((b) => {
                b.classList.toggle('online-exp-bridge--target', stepIndex === 3 && !bridges[parseInt(b.dataset.index, 10)]);
            });
        }

        function updateCupVisuals() {
            el.cupsRow.querySelectorAll('.online-exp-cup').forEach((cup) => {
                const i = parseInt(cup.dataset.index, 10);
                const water = cup.querySelector('.online-exp-cup__water');
                const role = cup.querySelector('.online-exp-cup__role');
                const color = cupColors[i] || mixFillColors[i];
                const isSource = SOURCE_INDEXES.includes(i);
                const mixLevel = parseFloat(water.style.height) || 0;

                if (color) {
                    water.style.backgroundColor = color;
                    water.style.height = isSource ? '78%' : water.style.height || '72%';
                    cup.classList.add('online-exp-cup--filled');
                    role.textContent = isSource ? 'Renkli' : 'Karışım';
                } else if (MIX_INDEXES.includes(i) && mixLevel > 8) {
                    role.textContent = 'Karışım';
                } else {
                    if (mixLevel <= 8) {
                        water.style.height = '0%';
                        water.style.backgroundColor = 'transparent';
                        cup.classList.remove('online-exp-cup--filled', 'online-exp-cup--glow');
                    }
                    role.textContent = isSource ? 'Renkli' : 'Boş';
                }
            });
        }

        function updateBridgeVisuals() {
            el.cupsRow.querySelectorAll('.online-exp-bridge').forEach((b) => {
                const i = parseInt(b.dataset.index, 10);
                b.classList.toggle('online-exp-bridge--on', bridges[i]);
            });
        }

        function pulseCup(index, kind) {
            const cup = el.cupsRow.querySelector('.online-exp-cup[data-index="' + index + '"]');
            if (!cup) return;
            cup.classList.remove('online-exp-cup--pulse-ok', 'online-exp-cup--pulse-warn');
            cup.classList.add(kind === 'ok' ? 'online-exp-cup--pulse-ok' : 'online-exp-cup--pulse-warn');
            setTimeout(() => cup.classList.remove('online-exp-cup--pulse-ok', 'online-exp-cup--pulse-warn'), 600);
        }

        function runAnimation() {
            if (animating) return;
            if (!sourcesReady() || !bridgesReady()) {
                flashHint('Önce tüm renkleri ve köprüleri hazırla.');
                return;
            }
            animating = true;
            el.btnStart.disabled = true;
            el.btnNext.disabled = true;
            el.hint.textContent = 'Renkler köprülerden yürüyor… İzle!';
            el.world.classList.add('online-exp-3d-world--animating');

            const plannedMixes = computeAllMixes();
            lastMixSummary = MIX_INDEXES.map((idx) => ({
                cup: idx + 1,
                hex: plannedMixes[idx],
                left: colorAt(idx - 1),
                right: colorAt(idx + 1),
            }));

            bridges.forEach((on, bridgeIdx) => {
                if (!on) return;
                const bridgeEl = el.cupsRow.querySelector('.online-exp-bridge[data-index="' + bridgeIdx + '"]');
                if (bridgeEl) {
                    setTimeout(() => {
                        bridgeEl.classList.add('online-exp-bridge--flowing');
                        const fromColor = colorAt(bridgeIdx) || colorAt(bridgeIdx + 1) || selectedColor;
                        bridgeEl.style.setProperty('--flow-color', fromColor);
                    }, 300 + bridgeIdx * 450);
                }
            });

            MIX_INDEXES.forEach((idx, n) => {
                setTimeout(() => {
                    const cup = el.cupsRow.querySelector('.online-exp-cup[data-index="' + idx + '"]');
                    if (!cup) return;
                    const mixHex = plannedMixes[idx];
                    mixFillColors[idx] = mixHex;
                    const water = cup.querySelector('.online-exp-cup__water');
                    water.style.backgroundColor = mixHex;
                    water.style.height = '72%';
                    cup.classList.add('online-exp-cup--filled', 'online-exp-cup--glow');
                    cup.querySelector('.online-exp-cup__role').textContent = 'Karışım';
                    spawnRipple(cup);
                }, 900 + n * 850);
            });

            setTimeout(() => {
                animating = false;
                el.world.classList.remove('online-exp-3d-world--animating');
                el.cupsRow.querySelectorAll('.online-exp-bridge--flowing').forEach((b) => {
                    b.classList.remove('online-exp-bridge--flowing');
                });
                stepIndex = TOTAL_STEPS;
                renderStep();
                flashHint('Deney bitti! Sonuçları sağ panelde gör.');
            }, 4200);
        }

        function spawnRipple(cup) {
            const ripple = document.createElement('span');
            ripple.className = 'online-exp-ripple';
            cup.querySelector('.online-exp-cup__body').appendChild(ripple);
            setTimeout(() => ripple.remove(), 800);
        }

        function buildResultSideHtml() {
            if (!lastMixSummary.length) {
                const planned = computeAllMixes();
                lastMixSummary = MIX_INDEXES.map((idx) => ({
                    cup: idx + 1,
                    hex: planned[idx],
                    left: colorAt(idx - 1),
                    right: colorAt(idx + 1),
                }));
            }
            let list = '';
            lastMixSummary.forEach((row) => {
                list +=
                    '<li class="flex items-start gap-2">' +
                    '<span class="mt-0.5 inline-block h-3 w-3 shrink-0 rounded-full" style="background:' +
                    row.hex +
                    '"></span>' +
                    '<span><strong>' +
                    row.cup +
                    '. bardak</strong> — yan renklerin karışımı</span>' +
                    '</li>';
            });

            return (
                '<div class="online-exp-result">' +
                '<p class="font-semibold text-violet-800">Senin karışımların</p>' +
                '<ul class="mt-2 space-y-2 text-sm text-slate-700">' +
                list +
                '</ul>' +
                '<p class="online-exp-science mt-3">Ara bardaklardaki renk, iki komşu bardaktaki renklerin <strong>karışımıdır</strong>. Su kağıt havluda emilerek yükselir (kapiler etki).</p>' +
                '</div>'
            );
        }

        function resetAll() {
            stepIndex = 0;
            animating = false;
            bridges.fill(false);
            cupColors.fill(null);
            mixFillColors.fill(null);
            lastMixSummary = [];
            el.btnStart.disabled = false;
            el.cupsRow.querySelectorAll('.online-exp-cup').forEach((c) => {
                c.classList.remove('online-exp-cup--glow');
                const w = c.querySelector('.online-exp-cup__water');
                w.style.height = '0%';
                w.style.backgroundColor = 'transparent';
            });
            renderStep();
        }

        function downloadScreenshot() {
            const canvas = document.createElement('canvas');
            const w = 960;
            const h = 480;
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            const grd = ctx.createLinearGradient(0, 0, w, h);
            grd.addColorStop(0, '#ede9fe');
            grd.addColorStop(1, '#ddd6fe');
            ctx.fillStyle = grd;
            ctx.fillRect(0, 0, w, h);

            ctx.fillStyle = '#5b21b6';
            ctx.font = 'bold 24px system-ui, sans-serif';
            ctx.fillText('Yürüyen Renkler — Online Deney Sonucu', 36, 48);

            ctx.font = '14px system-ui';
            ctx.fillStyle = '#64748b';
            ctx.fillText('Boya Etkinlik Laboratuvarı', 36, 72);

            const cupW = 72;
            const startX = 50;
            const baseY = 360;
            for (let i = 0; i < 7; i++) {
                const x = startX + i * (cupW + 22);
                ctx.strokeStyle = '#a78bfa';
                ctx.lineWidth = 3;
                ctx.strokeRect(x, baseY - 200, cupW, 200);
                let col = cupColors[i] || mixFillColors[i];
                if (col) {
                    const level = MIX_INDEXES.includes(i) ? 0.72 : 0.78;
                    ctx.fillStyle = col;
                    ctx.globalAlpha = 0.9;
                    ctx.fillRect(x + 5, baseY - 200 * level, cupW - 10, 200 * level);
                    ctx.globalAlpha = 1;
                }
                ctx.fillStyle = '#475569';
                ctx.font = 'bold 13px system-ui';
                ctx.fillText(String(i + 1), x + cupW / 2 - 4, baseY + 24);
            }

            canvas.toBlob((blob) => {
                if (!blob) return;
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'yuruyen-renkler-sonuc.png';
                a.click();
                URL.revokeObjectURL(a.href);
            });
        }

        function flashHint(msg) {
            el.hint.textContent = msg;
            el.hint.classList.add('online-exp-hint--flash');
            setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1400);
        }
    }
})();

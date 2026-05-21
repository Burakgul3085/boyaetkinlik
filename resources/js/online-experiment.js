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
            { id: 'red', hex: '#ef4444', label: 'Kırmızı' },
            { id: 'yellow', hex: '#eab308', label: 'Sarı' },
            { id: 'blue', hex: '#3b82f6', label: 'Mavi' },
        ];
        const MIX_HEX = ['#f97316', '#22c55e', '#a855f7'];
        const MIX_LABELS = ['Turuncu (kırmızı + sarı)', 'Yeşil (sarı + mavi)', 'Mor (mavi + kırmızı)'];
        const DEFAULT_SOURCES = ['#ef4444', '#eab308', '#3b82f6', '#ef4444'];

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
                    '<li>Gıda boyası: kırmızı, sarı, mavi</li>' +
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
                text: 'Önce paletten bir renk seç (kırmızı, sarı veya mavi). Sonra parlayan 1, 3, 5 veya 7 numaralı bardağa tıkla. Hepsi dolunca otomatik devam edebilirsin.',
                hint: 'İpucu: 1=kırmızı, 3=sarı, 5=mavi, 7=kırmızı (klasik düzen).',
                checklist: ['En az bir renk seç', '1, 3, 5 ve 7 dolu olmalı'],
                sideHtml:
                    '<p class="text-sm text-slate-600">Renkleri istediğin gibi de deneyebilirsin; animasyon yine çalışır. Dört ana bardak dolunca «Sonraki adım» açılır.</p>',
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
                text: 'Her şey hazır! Büyük butona bas: renkler köprülerden akacak, ara bardaklar turuncu, yeşil ve mor olacak.',
                hint: 'Animasyon birkaç saniye sürer; gözün masada olsun.',
                checklist: ['Renkler ve köprüler tamam'],
                sideHtml:
                    '<p class="text-sm text-slate-600">Bu bir <strong>bilgisayar modeli</strong>dir. Gerçekte suyun yükselmesi 30 dakika — 2 saat sürebilir; sabırlı ol!</p>',
            },
            {
                title: 'Sonuç ve bilim',
                text: 'Harika! Ara bardaklarda turuncu, yeşil ve mor oluştu. Gerçek deneyde de aynı renk karışımlarını görürsün.',
                hint: 'Sağdan yeniden dene veya sonucu PNG olarak indir.',
                checklist: [],
                sideHtml: '',
            },
        ];

        let stepIndex = 0;
        let selectedColor = PALETTE[0].hex;
        const cupColors = Array(7).fill(null);
        const bridges = Array(6).fill(false);
        let animating = false;

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
            cupsRow: document.getElementById('exp-cups-row'),
            arena: document.getElementById('exp-3d-arena'),
            world: document.getElementById('exp-3d-world'),
            flowLayer: document.getElementById('exp-flow-layer'),
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
                b.addEventListener('click', () => {
                    selectedColor = c.hex;
                    el.paletteColors.querySelectorAll('.online-exp-color-btn').forEach((btn) => {
                        btn.classList.toggle('online-exp-color-btn--active', btn.dataset.hex === selectedColor);
                    });
                });
                el.paletteColors.appendChild(b);
            });
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
            el.arena.classList.toggle('online-exp-3d-arena--run', stepIndex === 4 || isResult);

            if (stepIndex === 2 && !sourcesReady()) {
                applyDefaultSources();
            }

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

        function applyDefaultSources() {
            SOURCE_INDEXES.forEach((idx, n) => {
                if (!cupColors[idx]) cupColors[idx] = DEFAULT_SOURCES[n];
            });
        }

        function updateCupVisuals() {
            el.cupsRow.querySelectorAll('.online-exp-cup').forEach((cup) => {
                const i = parseInt(cup.dataset.index, 10);
                const water = cup.querySelector('.online-exp-cup__water');
                const role = cup.querySelector('.online-exp-cup__role');
                const color = cupColors[i];
                const isSource = SOURCE_INDEXES.includes(i);
                const mixLevel = parseFloat(water.style.height) || 0;

                if (color) {
                    water.style.backgroundColor = color;
                    water.style.height = isSource ? '78%' : water.style.height;
                    cup.classList.add('online-exp-cup--filled');
                    role.textContent = 'Renkli';
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

            bridges.forEach((on, bridgeIdx) => {
                if (!on) return;
                const bridgeEl = el.cupsRow.querySelector('.online-exp-bridge[data-index="' + bridgeIdx + '"]');
                if (bridgeEl) {
                    setTimeout(() => {
                        bridgeEl.classList.add('online-exp-bridge--flowing');
                        const leftCup = SOURCE_INDEXES.includes(bridgeIdx) || MIX_INDEXES.includes(bridgeIdx - 1)
                            ? bridgeIdx
                            : bridgeIdx;
                        const fromColor = cupColors[bridgeIdx] || cupColors[bridgeIdx + 1] || selectedColor;
                        bridgeEl.style.setProperty('--flow-color', fromColor);
                    }, 300 + bridgeIdx * 450);
                }
            });

            MIX_INDEXES.forEach((idx, n) => {
                setTimeout(() => {
                    const cup = el.cupsRow.querySelector('.online-exp-cup[data-index="' + idx + '"]');
                    if (!cup) return;
                    const water = cup.querySelector('.online-exp-cup__water');
                    water.style.backgroundColor = MIX_HEX[n];
                    water.style.height = '72%';
                    cup.classList.add('online-exp-cup--filled', 'online-exp-cup--glow');
                    cup.querySelector('.online-exp-cup__role').textContent = 'Doldu!';
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
            return (
                '<div class="online-exp-result">' +
                '<p class="font-semibold text-violet-800">Ne öğrendik?</p>' +
                '<ul class="mt-2 space-y-2 text-sm text-slate-700">' +
                '<li><span style="color:#f97316">■</span> 2. bardak: ' +
                MIX_LABELS[0] +
                '</li>' +
                '<li><span style="color:#22c55e">■</span> 4. bardak: ' +
                MIX_LABELS[1] +
                '</li>' +
                '<li><span style="color:#a855f7">■</span> 6. bardak: ' +
                MIX_LABELS[2] +
                '</li>' +
                '</ul>' +
                '<p class="online-exp-science mt-3">Su kağıt havluda <strong>emilerek</strong> yükselir (kapiler etki). Renkler de suyla birlikte taşınır — buna «yürüyen renkler» denir.</p>' +
                '</div>'
            );
        }

        function resetAll() {
            stepIndex = 0;
            animating = false;
            bridges.fill(false);
            cupColors.fill(null);
            el.btnStart.disabled = false;
            el.flowLayer.innerHTML = '';
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
                let col = cupColors[i];
                if (!col && MIX_INDEXES.includes(i)) {
                    const mi = MIX_INDEXES.indexOf(i);
                    col = MIX_HEX[mi];
                }
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

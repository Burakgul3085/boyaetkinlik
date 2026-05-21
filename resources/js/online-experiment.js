/**
 * Online Deney Laboratuvarı — Boya Etkinlik (dış API yok)
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
        const SOURCE_INDEXES = [0, 2, 4, 6];
        const MIX_INDEXES = [1, 3, 5];
        const PALETTE = [
            { id: 'red', hex: '#ef4444', label: 'Kırmızı' },
            { id: 'yellow', hex: '#eab308', label: 'Sarı' },
            { id: 'blue', hex: '#3b82f6', label: 'Mavi' },
        ];
        const MIX_HEX = ['#f97316', '#22c55e', '#a855f7'];
        const DEFAULT_SOURCES = ['#ef4444', '#eab308', '#3b82f6', '#ef4444'];

        const steps = [
            {
                title: 'Hoş geldin!',
                text: 'Yürüyen renkler deneyine hazır mısın? Bilgisayarda renklerin birleşmesini izleyeceksin.',
                hint: 'Başlamak için «Sonraki»ye bas.',
            },
            {
                title: 'Renkleri yerleştir',
                text: 'Önce bir renk seç, sonra 1 · 3 · 5 · 7 numaralı bardaklara tıkla.',
                hint: 'Dolu bardaklar renkli görünür.',
            },
            {
                title: 'Kağıt havlu köprüleri',
                text: 'Bardaklar arasındaki her çizgiye tıklayarak kağıt havlu köprüsü kur.',
                hint: 'Altı köprü de hazır olmalı.',
            },
            {
                title: 'Deneyi başlat',
                text: 'Her şey hazırsa «Deneyi başlat» ile renklerin yürümesini izle.',
                hint: 'Animasyon birkaç saniye sürer.',
            },
        ];

        let stepIndex = 0;
        let selectedColor = PALETTE[0].hex;
        const cupColors = Array(7).fill(null);
        const bridges = Array(6).fill(false);
        let animating = false;

        const el = {
            badge: document.getElementById('exp-step-badge'),
            title: document.getElementById('exp-step-title'),
            text: document.getElementById('exp-step-text'),
            checklist: document.getElementById('exp-checklist'),
            btnPrev: document.getElementById('exp-btn-prev'),
            btnNext: document.getElementById('exp-btn-next'),
            palette: document.getElementById('exp-palette'),
            paletteColors: document.getElementById('exp-palette-colors'),
            cupsRow: document.getElementById('exp-cups-row'),
            bridges: document.getElementById('exp-bridges'),
            hint: document.getElementById('exp-stage-hint'),
            btnStart: document.getElementById('exp-btn-start'),
            sideBody: document.getElementById('exp-side-body'),
            sideActions: document.getElementById('exp-side-actions'),
            btnRetry: document.getElementById('exp-btn-retry'),
            btnScreenshot: document.getElementById('exp-btn-screenshot'),
            stageInner: document.getElementById('exp-stage-inner'),
        };

        buildCups();
        buildPalette();
        buildBridges();
        bindEvents();
        renderStep();

        function buildCups() {
            el.cupsRow.innerHTML = '';
            for (let i = 0; i < 7; i++) {
                const cup = document.createElement('button');
                cup.type = 'button';
                cup.className = 'online-exp-cup';
                cup.dataset.index = String(i);
                cup.innerHTML =
                    '<span class="online-exp-cup__num">' +
                    (i + 1) +
                    '</span>' +
                    '<span class="online-exp-cup__glass"></span>' +
                    '<span class="online-exp-cup__water"></span>';
                cup.addEventListener('click', () => onCupClick(i));
                el.cupsRow.appendChild(cup);
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
                b.dataset.hex = c.hex;
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

        function buildBridges() {
            el.bridges.innerHTML = '';
            for (let i = 0; i < 6; i++) {
                const b = document.createElement('button');
                b.type = 'button';
                b.className = 'online-exp-bridge';
                b.dataset.index = String(i);
                b.innerHTML = '<span class="online-exp-bridge__towel"></span>';
                b.addEventListener('click', () => onBridgeClick(i));
                el.bridges.appendChild(b);
            }
        }

        function bindEvents() {
            el.btnPrev.addEventListener('click', () => {
                if (stepIndex > 0 && !animating) {
                    stepIndex--;
                    renderStep();
                }
            });
            el.btnNext.addEventListener('click', () => {
                if (animating) return;
                if (stepIndex < 3) {
                    if (stepIndex === 1 && !sourcesReady()) return;
                    if (stepIndex === 2 && !bridgesReady()) return;
                    stepIndex++;
                    renderStep();
                }
            });
            el.btnStart.addEventListener('click', runAnimation);
            el.btnRetry.addEventListener('click', resetAll);
            el.btnScreenshot.addEventListener('click', downloadScreenshot);
        }

        function onCupClick(index) {
            if (stepIndex !== 1 || animating) return;
            if (!SOURCE_INDEXES.includes(index)) {
                flashHint('Sadece 1, 3, 5 ve 7 numaralı bardaklara renk koy.');
                return;
            }
            cupColors[index] = selectedColor;
            updateCupVisuals();
        }

        function onBridgeClick(index) {
            if (stepIndex !== 2 || animating) return;
            bridges[index] = !bridges[index];
            updateBridgeVisuals();
        }

        function sourcesReady() {
            return SOURCE_INDEXES.every((i) => cupColors[i] !== null);
        }

        function bridgesReady() {
            return bridges.every(Boolean);
        }

        function renderStep() {
            const onColorStep = stepIndex === 1;
            const onBridgeStep = stepIndex === 2;
            const onStartStep = stepIndex === 3;

            el.badge.textContent = 'Adım ' + (stepIndex + 1) + ' / 4';
            el.title.textContent = steps[stepIndex].title;
            el.text.textContent = steps[stepIndex].text;
            el.hint.textContent = steps[stepIndex].hint;
            el.btnPrev.disabled = stepIndex === 0 || animating;
            el.btnNext.hidden = onStartStep;
            el.btnNext.disabled = animating;
            el.palette.hidden = !onColorStep;
            el.bridges.hidden = !onBridgeStep;
            el.btnStart.hidden = !onStartStep;
            el.checklist.hidden = true;
            el.sideActions.hidden = true;

            if (onColorStep && !sourcesReady()) {
                applyDefaultSources();
            }
            updateCupVisuals();
            updateBridgeVisuals();

            if (stepIndex === 0) {
                el.sideBody.innerHTML =
                    '<p class="text-sm text-slate-600">Bu bir <strong>model</strong>dir; gerçek deneyde suyun yükselmesi biraz daha uzun sürer.</p>';
            } else if (!onStartStep) {
                el.sideBody.innerHTML =
                    '<p class="text-sm text-slate-600">' + escapeHtml(steps[stepIndex].hint) + '</p>';
            }
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
                const color = cupColors[i];
                if (color) {
                    water.style.backgroundColor = color;
                    water.style.height = SOURCE_INDEXES.includes(i) ? '72%' : '0%';
                    cup.classList.add('online-exp-cup--filled');
                } else {
                    water.style.height = '0%';
                    water.style.backgroundColor = 'transparent';
                    cup.classList.remove('online-exp-cup--filled');
                }
            });
        }

        function updateBridgeVisuals() {
            el.bridges.querySelectorAll('.online-exp-bridge').forEach((b) => {
                const i = parseInt(b.dataset.index, 10);
                b.classList.toggle('online-exp-bridge--on', bridges[i]);
            });
        }

        function runAnimation() {
            if (animating) return;
            if (!sourcesReady() || !bridgesReady()) {
                flashHint('Önce renkleri ve tüm köprüleri hazırla.');
                return;
            }
            animating = true;
            el.btnStart.disabled = true;
            el.hint.textContent = 'Renkler yürüyor…';

            MIX_INDEXES.forEach((idx, n) => {
                setTimeout(() => {
                    const cup = el.cupsRow.querySelector('[data-index="' + idx + '"]');
                    const water = cup.querySelector('.online-exp-cup__water');
                    water.style.backgroundColor = MIX_HEX[n];
                    water.style.height = '68%';
                    cup.classList.add('online-exp-cup--filled', 'online-exp-cup--glow');
                }, 400 + n * 700);
            });

            setTimeout(() => {
                animating = false;
                showResult();
            }, 3200);
        }

        function showResult() {
            stepIndex = 4;
            el.badge.textContent = 'Tamamlandı!';
            el.title.textContent = 'Harika! İşte sonucun';
            el.text.textContent = 'Ara bardaklarda turuncu, yeşil ve mor oluştu. Gerçek deneyde de benzer renkler görürsün.';
            el.palette.hidden = true;
            el.bridges.hidden = true;
            el.btnStart.hidden = true;
            el.btnNext.hidden = true;
            el.btnPrev.disabled = false;
            el.hint.textContent = 'Yeniden denemek veya sonucu indirmek için sağ paneli kullan.';

            el.sideBody.innerHTML =
                '<div class="online-exp-result">' +
                '<p class="font-semibold text-violet-800">Oluşan renkler</p>' +
                '<ul class="mt-2 space-y-1 text-sm text-slate-700">' +
                '<li>2. bardak: <span style="color:#f97316">■</span> Turuncu (kırmızı + sarı)</li>' +
                '<li>4. bardak: <span style="color:#22c55e">■</span> Yeşil (sarı + mavi)</li>' +
                '<li>6. bardak: <span style="color:#a855f7">■</span> Mor (mavi + kırmızı)</li>' +
                '</ul>' +
                '<p class="mt-3 text-xs text-slate-500">Su kağıt havluda yükselerek renkleri taşıdı — buna emilim denir.</p>' +
                '</div>';
            el.sideActions.hidden = false;

            try {
                localStorage.setItem('exp_lab_done_' + (root.dataset.articleUrl || 'walk'), '1');
            } catch (e) {}
        }

        function resetAll() {
            stepIndex = 0;
            animating = false;
            bridges.fill(false);
            cupColors.fill(null);
            el.btnStart.disabled = false;
            el.cupsRow.querySelectorAll('.online-exp-cup').forEach((c) => c.classList.remove('online-exp-cup--glow'));
            renderStep();
        }

        function downloadScreenshot() {
            const canvas = document.createElement('canvas');
            const w = 900;
            const h = 420;
            canvas.width = w;
            canvas.height = h;
            const ctx = canvas.getContext('2d');
            if (!ctx) return;

            const grd = ctx.createLinearGradient(0, 0, w, h);
            grd.addColorStop(0, '#f5f3ff');
            grd.addColorStop(1, '#ede9fe');
            ctx.fillStyle = grd;
            ctx.fillRect(0, 0, w, h);

            ctx.fillStyle = '#5b21b6';
            ctx.font = 'bold 22px system-ui, sans-serif';
            ctx.fillText('Online Deney Sonucu — Yürüyen Renkler', 40, 45);

            const cupW = 70;
            const startX = 80;
            const baseY = 320;
            for (let i = 0; i < 7; i++) {
                const x = startX + i * (cupW + 18);
                ctx.strokeStyle = '#c4b5fd';
                ctx.lineWidth = 3;
                ctx.strokeRect(x, baseY - 180, cupW, 180);
                const col = cupColors[i] || (MIX_INDEXES.includes(i) ? MIX_HEX[MIX_INDEXES.indexOf(i)] : '#e2e8f0');
                if (col && col !== '#e2e8f0') {
                    const level = MIX_INDEXES.includes(i) ? 0.68 : 0.72;
                    ctx.fillStyle = col;
                    ctx.globalAlpha = 0.85;
                    ctx.fillRect(x + 4, baseY - 180 * level, cupW - 8, 180 * level);
                    ctx.globalAlpha = 1;
                }
                ctx.fillStyle = '#64748b';
                ctx.font = '12px system-ui';
                ctx.fillText(String(i + 1), x + cupW / 2 - 4, baseY + 22);
            }

            canvas.toBlob((blob) => {
                if (!blob) return;
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                a.download = 'online-deney-sonuc.png';
                a.click();
                URL.revokeObjectURL(a.href);
            });
        }

        function flashHint(msg) {
            el.hint.textContent = msg;
            el.hint.classList.add('online-exp-hint--flash');
            setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1200);
        }

        function escapeHtml(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }
    }
})();

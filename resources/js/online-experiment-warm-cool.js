/**
 * Sıcak & Soğuk Renkler
 */
(function () {
    const app = document.getElementById('online-exp-app');
    if (!app) return;

    const TOTAL_STEPS = 4;
    const CHIPS = [
        { id: 'r', hex: '#ef4444', label: 'Kırmızı', zone: 'warm' },
        { id: 'o', hex: '#f97316', label: 'Turuncu', zone: 'warm' },
        { id: 'y', hex: '#eab308', label: 'Sarı', zone: 'warm' },
        { id: 'p', hex: '#ec4899', label: 'Pembe', zone: 'warm' },
        { id: 'b', hex: '#3b82f6', label: 'Mavi', zone: 'cool' },
        { id: 't', hex: '#06b6d4', label: 'Turkuaz', zone: 'cool' },
        { id: 'g', hex: '#22c55e', label: 'Yeşil', zone: 'cool' },
        { id: 'v', hex: '#a855f7', label: 'Mor', zone: 'cool' },
    ];

    const steps = [
        {
            title: 'Sıcak ve soğuk renk',
            text: 'Boyama sayfalarında bazı renkler sıcak (güneş, neşe), bazıları soğuk (su, huzur) his verir.',
            hint: 'Kompozisyon için önemli bir kavram.',
            checklist: ['8 rengi grupla', 'Doğru tarafa yerleştir'],
            sideHtml: '<p class="text-xs text-slate-600">Sıcak: kırmızı, turuncu, sarı tonları. Soğuk: mavi, yeşil, mor tonları.</p>',
        },
        {
            title: 'Renkleri tanı',
            text: 'Ortadaki renk kutularına bak. Her birini doğru tarafa yerleştireceksin.',
            hint: 'Sonraki adımda yerleştirme başlar.',
            checklist: [],
            sideHtml: '',
        },
        {
            title: 'Yerleştir',
            text: 'Önce ortadan bir renk seç (parlar), sonra sıcak ☀️ veya soğuk ❄️ kutusuna tıkla. Tüm renkler yerleşince kontrol et.',
            hint: 'Yanlış yerleştirirsen tekrar seçip taşı.',
            checklist: ['8 renk de yerleşti'],
            sideHtml: '',
        },
        {
            title: 'Tebrikler!',
            text: 'Sıcak ve soğuk renkleri ayırt ettin. Resimlerinde gökyüzü için soğuk, güneş için sıcak renkler kullanabilirsin.',
            hint: 'PNG kaydet veya baştan dene.',
            checklist: [],
            sideHtml: '',
        },
    ];

    let stepIndex = 0;
    let selectedChipId = null;
    const placements = {};

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
        pool: document.getElementById('exp-warmcool-pool'),
        warmDrop: document.getElementById('exp-warm-drop'),
        coolDrop: document.getElementById('exp-cool-drop'),
        checkBtn: document.getElementById('exp-warmcool-check'),
    };

    buildPool();
    bindEvents();
    renderStep();

    function buildPool() {
        if (!el.pool) return;
        el.pool.innerHTML = '';
        CHIPS.forEach((chip) => {
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'online-exp-warmcool-chip';
            b.style.backgroundColor = chip.hex;
            b.dataset.id = chip.id;
            b.title = chip.label;
            b.setAttribute('aria-label', chip.label);
            b.addEventListener('click', () => selectChip(chip.id));
            el.pool.appendChild(b);
        });
    }

    function selectChip(id) {
        if (stepIndex !== 2) return;
        selectedChipId = id;
        el.pool.querySelectorAll('.online-exp-warmcool-chip').forEach((c) => {
            c.classList.toggle('online-exp-warmcool-chip--selected', c.dataset.id === id);
        });
        flashHint('Şimdi sıcak veya soğuk kutusuna tıkla.');
    }

    function placeInZone(zone) {
        if (!selectedChipId || stepIndex !== 2) return;
        placements[selectedChipId] = zone;
        renderPlacements();
        selectedChipId = null;
        el.pool.querySelectorAll('.online-exp-warmcool-chip').forEach((c) => {
            const placed = placements[c.dataset.id];
            c.classList.toggle('online-exp-warmcool-chip--placed', !!placed);
            c.classList.remove('online-exp-warmcool-chip--selected');
            c.hidden = !!placed;
        });
        if (Object.keys(placements).length === CHIPS.length) {
            if (el.checkBtn) el.checkBtn.hidden = false;
            flashHint('Hepsi yerleşti! «Kontrol et»e bas.');
        }
    }

    function renderPlacements() {
        [el.warmDrop, el.coolDrop].forEach((drop) => {
            if (!drop) return;
            drop.innerHTML = '';
        });
        CHIPS.forEach((chip) => {
            const z = placements[chip.id];
            if (!z) return;
            const drop = z === 'warm' ? el.warmDrop : el.coolDrop;
            const pill = document.createElement('button');
            pill.type = 'button';
            pill.className = 'online-exp-warmcool-placed';
            pill.style.backgroundColor = chip.hex;
            pill.title = chip.label + ' — geri al';
            pill.addEventListener('click', () => {
                delete placements[chip.id];
                renderPlacements();
                renderPoolVisibility();
            });
            drop.appendChild(pill);
        });
    }

    function renderPoolVisibility() {
        el.pool.querySelectorAll('.online-exp-warmcool-chip').forEach((c) => {
            const placed = placements[c.dataset.id];
            c.hidden = !!placed;
            c.classList.remove('online-exp-warmcool-chip--selected');
        });
        if (el.checkBtn) el.checkBtn.hidden = Object.keys(placements).length !== CHIPS.length;
    }

    function validate() {
        let ok = 0;
        CHIPS.forEach((chip) => {
            if (placements[chip.id] === chip.zone) ok++;
        });
        return ok;
    }

    function bindEvents() {
        el.btnPrev.addEventListener('click', () => {
            if (stepIndex > 0) {
                stepIndex--;
                renderStep();
            }
        });
        el.btnNext.addEventListener('click', () => {
            if (stepIndex === 2 && Object.keys(placements).length < CHIPS.length) {
                flashHint('Tüm renkleri yerleştir.');
                return;
            }
            if (stepIndex < TOTAL_STEPS - 1) {
                stepIndex++;
                renderStep();
            }
        });
        document.querySelector('[data-zone="warm"]')?.addEventListener('click', (e) => {
            if (!e.target.closest('.online-exp-warmcool-placed')) placeInZone('warm');
        });
        document.querySelector('[data-zone="cool"]')?.addEventListener('click', (e) => {
            if (!e.target.closest('.online-exp-warmcool-placed')) placeInZone('cool');
        });
        if (el.checkBtn) {
            el.checkBtn.addEventListener('click', () => {
                const score = validate();
                if (score === CHIPS.length) {
                    flashHint('Mükemmel! Hepsi doğru yerde.');
                    stepIndex = TOTAL_STEPS;
                    renderStep();
                } else {
                    flashHint(score + '/' + CHIPS.length + ' doğru. Yanlış olanları değiştir.');
                    CHIPS.forEach((chip) => {
                        if (placements[chip.id] !== chip.zone) {
                            delete placements[chip.id];
                        }
                    });
                    renderPlacements();
                    renderPoolVisibility();
                }
            });
        }
        el.btnRetry.addEventListener('click', resetAll);
        el.btnScreenshot.addEventListener('click', downloadScreenshot);
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
        if (el.checkBtn) el.checkBtn.hidden = stepIndex !== 2 || Object.keys(placements).length < CHIPS.length;
        el.sideActions.hidden = !isResult;

        document.getElementById('exp-warmcool-board').classList.toggle('online-exp-warmcool-board--active', stepIndex >= 2);
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
            '<div class="online-exp-result"><p class="font-semibold text-violet-800">İpucu</p>' +
            '<p class="mt-2 text-xs text-slate-600">Manzara: gökyüzü <strong>soğuk</strong>, güneş ve çiçekler <strong>sıcak</strong>. Kontrast resmi canlandırır.</p></div>'
        );
    }

    function resetAll() {
        stepIndex = 0;
        selectedChipId = null;
        Object.keys(placements).forEach((k) => delete placements[k]);
        buildPool();
        renderPlacements();
        renderPoolVisibility();
        renderStep();
    }

    function downloadScreenshot() {
        const board = document.getElementById('exp-warmcool-board');
        if (!board) return;
        flashHint('Ekran görüntüsü için tarayıcıda yazdır veya ekran alıntısı kullan.');
    }

    function flashHint(msg) {
        el.hint.textContent = msg;
        el.hint.classList.add('online-exp-hint--flash');
        setTimeout(() => el.hint.classList.remove('online-exp-hint--flash'), 1400);
    }
})();

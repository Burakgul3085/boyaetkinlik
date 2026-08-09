/**
 * Private gift page interactions — fully isolated IIFE.
 */
(() => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const preloader = document.getElementById('pg-preloader');
    const progressBar = document.getElementById('pg-progress-bar');
    const scenes = Array.from(document.querySelectorAll('[data-pg-scene]'));

    const moodMessages = {
        tired: 'O zaman bugün kendine biraz daha nazik davran. Her şeyi bugün çözmek zorunda değilsin.',
        confused: 'Bazı hislerin adı hemen konulmuyor. Bazen zaman en iyi açıklamayı kendi yapıyor.',
        ok: "Bazen 'idare ediyorum' bile yeterince büyük bir başarıdır.",
        better: 'Güzel. Umarım yarın bu cümleyi biraz daha rahat söylersin.',
    };

    const hardDayMessages = [
        'Bugünün kötü olması, yarının da kötü olacağı anlamına gelmiyor.',
        'Her şeyi bugün çözmek zorunda değilsin.',
        'Bazen sistemin ihtiyacı olan şey sadece biraz dinlenmektir.',
        'Kendine başkalarına davrandığından biraz daha nazik davran.',
        'Bugün yalnızca günü tamamlamak bile yeterli olabilir.',
        'happiness.exe temporarily unavailable\nrecovery mode started...\n\nÖneri: biraz müzik, biraz kahve ve biraz nefes almak.',
    ];

    const terminalLines = [
        '> checking today...',
        '> finding something worth smiling about...',
        '> result: found',
        '',
        'smile.status = "loading";',
        'hope.level++;',
        '',
        'done.',
    ];

    function hidePreloader() {
        preloader?.classList.add('is-done');
    }

    if (document.readyState === 'complete') {
        setTimeout(hidePreloader, 120);
    } else {
        window.addEventListener('load', () => setTimeout(hidePreloader, 120), { once: true });
    }

    function updateProgress() {
        if (!progressBar) return;
        const idx = scenes.findIndex((s) => s.classList.contains('is-active'));
        if (idx < 0) return;
        const pct = ((idx + 1) / scenes.length) * 100;
        progressBar.style.height = `${pct}%`;
    }

    function showScene(target) {
        if (!target) return;
        scenes.forEach((scene) => {
            const active = scene === target;
            scene.hidden = !active;
            scene.classList.toggle('is-active', active);
        });
        updateProgress();
        if (!reduceMotion) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            target.scrollIntoView(true);
        }
        document.dispatchEvent(new CustomEvent('pg:scene', { detail: { id: target.id } }));
    }

    document.querySelectorAll('[data-pg-next]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sel = btn.getAttribute('data-pg-next');
            const next = sel ? document.querySelector(sel) : null;
            showScene(next);
        });
    });

    // Mood
    const moodReply = document.getElementById('pg-mood-reply');
    const moodContinue = document.getElementById('pg-mood-continue');
    document.querySelectorAll('[data-mood]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const key = btn.getAttribute('data-mood') || '';
            document.querySelectorAll('[data-mood]').forEach((b) => b.classList.remove('is-selected'));
            btn.classList.add('is-selected');
            if (moodReply) {
                moodReply.hidden = false;
                moodReply.textContent = moodMessages[key] || '';
            }
            if (moodContinue) moodContinue.hidden = false;
            try {
                localStorage.setItem('pg-mood', key);
            } catch (_) { /* ignore */ }
        });
    });

    // Terminal typing
    const terminalBody = document.getElementById('pg-terminal-body');
    let terminalStarted = false;

    function runTerminal() {
        if (!terminalBody || terminalStarted) return;
        terminalStarted = true;
        let line = 0;
        let col = 0;
        let buffer = '';

        if (reduceMotion) {
            terminalBody.textContent = terminalLines.join('\n');
            return;
        }

        const step = () => {
            if (line >= terminalLines.length) return;
            const current = terminalLines[line];
            if (col <= current.length) {
                buffer = terminalLines.slice(0, line).join('\n')
                    + (line > 0 ? '\n' : '')
                    + current.slice(0, col);
                terminalBody.textContent = buffer;
                col += 1;
                window.setTimeout(step, current === '' ? 80 : 18);
            } else {
                line += 1;
                col = 0;
                window.setTimeout(step, 160);
            }
        };
        step();
    }

    // Hard day
    const hardReply = document.getElementById('pg-hard-reply');
    const hardBtn = document.getElementById('pg-hard-day');
    hardBtn?.addEventListener('click', () => {
        if (!hardReply) return;
        const msg = hardDayMessages[Math.floor(Math.random() * hardDayMessages.length)];
        hardReply.textContent = msg;
        hardReply.hidden = false;
    });

    // Portrait reveal
    const portrait = document.querySelector('[data-pg-reveal]');
    function revealPortrait() {
        portrait?.classList.add('is-visible');
    }

    // Final sequence + flower
    let finalStarted = false;
    function runFinal() {
        if (finalStarted) return;
        finalStarted = true;
        const lines = Array.from(document.querySelectorAll('#pg-final-lines .pg-final-line'));
        const flowerWrap = document.getElementById('pg-flower-wrap');
        const flower = document.getElementById('pg-flower');
        const after = document.getElementById('pg-after-flower');

        const delays = reduceMotion ? [0, 0, 0, 0, 0] : [0, 900, 1800, 2700, 3600];

        lines.forEach((el, i) => {
            window.setTimeout(() => {
                el.hidden = false;
                el.classList.add('is-in');
                if (i === lines.length - 1) {
                    window.setTimeout(() => {
                        if (flowerWrap) flowerWrap.hidden = false;
                        flower?.classList.add('is-grow');
                        window.setTimeout(() => {
                            if (after) after.hidden = false;
                        }, reduceMotion ? 50 : 2300);
                    }, reduceMotion ? 100 : 700);
                }
            }, delays[i] || 0);
        });
    }

    document.addEventListener('pg:scene', (e) => {
        const id = e.detail?.id;
        if (id === 'pg-scene-3') runTerminal();
        if (id === 'pg-scene-6') startYoutube();
        if (id === 'pg-scene-8') {
            window.setTimeout(revealPortrait, reduceMotion ? 50 : 350);
        }
        if (id === 'pg-scene-9') runFinal();
    });

    // YouTube — sahne 6'da 1:42'den autoplay (önceki "Devam" jesti sayesinde)
    let youtubeStarted = false;

    function startYoutube() {
        const host = document.getElementById('pg-yt-host');
        const wrap = document.getElementById('pg-yt');
        const playBtn = document.getElementById('pg-yt-play');
        if (!host || !wrap || youtubeStarted) return;

        const embedUrl = wrap.getAttribute('data-embed-url');
        if (!embedUrl) return;

        youtubeStarted = true;
        host.innerHTML = '';

        const iframe = document.createElement('iframe');
        iframe.src = embedUrl;
        iframe.title = 'Gripin — Nasip';
        iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share';
        iframe.allowFullscreen = true;
        iframe.referrerPolicy = 'strict-origin-when-cross-origin';
        host.appendChild(iframe);

        // Tarayıcı engellerse kullanıcı butona basabilir
        if (playBtn) {
            window.setTimeout(() => {
                playBtn.hidden = false;
            }, 1800);
            playBtn.addEventListener('click', () => {
                iframe.src = embedUrl;
                playBtn.hidden = true;
            }, { once: true });
        }
    }

    // Smile mission
    const smileBtn = document.getElementById('pg-smile-btn');
    const missionDone = document.getElementById('pg-mission-done');
    const missionText = document.getElementById('pg-mission-text');
    const missionCode = document.getElementById('pg-mission-code');
    const closing = document.getElementById('pg-closing');

    smileBtn?.addEventListener('click', () => {
        smileBtn.hidden = true;
        if (missionDone) missionDone.hidden = false;
        window.setTimeout(() => {
            if (missionText) missionText.hidden = false;
        }, reduceMotion ? 0 : 900);
        window.setTimeout(() => {
            if (missionCode) missionCode.hidden = false;
            if (closing) closing.hidden = false;
        }, reduceMotion ? 50 : 1600);
    });

    // Easter egg modal
    const modal = document.getElementById('pg-modal');
    const easter = document.getElementById('pg-easter');
    let lastFocus = null;

    function openModal() {
        if (!modal) return;
        lastFocus = document.activeElement;
        modal.hidden = false;
        modal.querySelector('button')?.focus();
    }

    function closeModal() {
        if (!modal || modal.hidden) return;
        modal.hidden = true;
        if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
    }

    easter?.addEventListener('click', openModal);
    modal?.querySelectorAll('[data-pg-modal-close]').forEach((el) => {
        el.addEventListener('click', closeModal);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeModal();
    });

    // Particles
    const canvas = document.getElementById('pg-particles');
    if (canvas && !reduceMotion && canvas.getContext) {
        const ctx = canvas.getContext('2d');
        let particles = [];
        let raf = 0;
        let running = true;
        let w = 0;
        let h = 0;

        function resize() {
            w = window.innerWidth;
            h = window.innerHeight;
            const dpr = Math.min(window.devicePixelRatio || 1, 2);
            canvas.width = Math.floor(w * dpr);
            canvas.height = Math.floor(h * dpr);
            canvas.style.width = `${w}px`;
            canvas.style.height = `${h}px`;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }

        function spawn() {
            const count = w < 480 ? 16 : 24;
            particles = Array.from({ length: count }, () => ({
                x: Math.random() * w,
                y: Math.random() * h,
                r: 0.6 + Math.random() * 1.6,
                vy: 0.08 + Math.random() * 0.25,
                vx: (Math.random() - 0.5) * 0.12,
                a: 0.15 + Math.random() * 0.35,
            }));
        }

        function tick() {
            if (!running || !ctx) return;
            ctx.clearRect(0, 0, w, h);
            for (const p of particles) {
                p.x += p.vx;
                p.y -= p.vy;
                if (p.y < -4) {
                    p.y = h + 4;
                    p.x = Math.random() * w;
                }
                ctx.beginPath();
                ctx.fillStyle = `rgba(201, 173, 122, ${p.a})`;
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fill();
            }
            raf = window.requestAnimationFrame(tick);
        }

        resize();
        spawn();
        tick();

        let resizeT = 0;
        window.addEventListener('resize', () => {
            window.clearTimeout(resizeT);
            resizeT = window.setTimeout(() => {
                resize();
                spawn();
            }, 150);
        });

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                running = false;
                window.cancelAnimationFrame(raf);
            } else {
                running = true;
                tick();
            }
        });
    }

    updateProgress();
})();

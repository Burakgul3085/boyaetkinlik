<main class="online-exp-stage online-exp-stage--trace" aria-label="Çizgi tamamlama stüdyosu">
    <p class="online-exp-stage-label">Çizgi çalışması — kalemin çizginin üzerinden yavaşça geçsin</p>
    <div class="online-exp-stage__inner online-exp-stage__inner--trace" id="exp-stage-inner">
        <div class="online-exp-trace-studio" id="exp-trace-studio">
            <div class="online-exp-trace-patterns" id="exp-trace-patterns" role="listbox" aria-label="Desen seç">
                <button type="button" class="online-exp-trace-card online-exp-trace-card--active" data-pattern="ev" role="option">
                    <canvas class="online-exp-trace-card__preview" data-preview="ev" width="80" height="56" aria-hidden="true"></canvas>
                    <span class="online-exp-trace-card__title">Ev</span>
                    <span class="online-exp-trace-card__badge">Kolay</span>
                </button>
                <button type="button" class="online-exp-trace-card" data-pattern="kelebek" role="option">
                    <canvas class="online-exp-trace-card__preview" data-preview="kelebek" width="80" height="56" aria-hidden="true"></canvas>
                    <span class="online-exp-trace-card__title">Kelebek</span>
                    <span class="online-exp-trace-card__badge">Orta</span>
                </button>
                <button type="button" class="online-exp-trace-card" data-pattern="cicek" role="option">
                    <canvas class="online-exp-trace-card__preview" data-preview="cicek" width="80" height="56" aria-hidden="true"></canvas>
                    <span class="online-exp-trace-card__title">Çiçek</span>
                    <span class="online-exp-trace-card__badge">Kolay</span>
                </button>
                <button type="button" class="online-exp-trace-card" data-pattern="yildiz" role="option">
                    <canvas class="online-exp-trace-card__preview" data-preview="yildiz" width="80" height="56" aria-hidden="true"></canvas>
                    <span class="online-exp-trace-card__title">Yıldız</span>
                    <span class="online-exp-trace-card__badge">Kolay</span>
                </button>
                <button type="button" class="online-exp-trace-card" data-pattern="dalga" role="option">
                    <canvas class="online-exp-trace-card__preview" data-preview="dalga" width="80" height="56" aria-hidden="true"></canvas>
                    <span class="online-exp-trace-card__title">Dalga</span>
                    <span class="online-exp-trace-card__badge">Orta</span>
                </button>
            </div>

            <div class="online-exp-trace-workspace" id="exp-trace-workspace">
                <div class="online-exp-trace-toolbar" id="exp-trace-toolbar" hidden>
                    <div class="online-exp-trace-toolbar__left">
                        <span class="online-exp-trace-toolbar__label">Kalem</span>
                        <button type="button" class="online-exp-trace-brush online-exp-trace-brush--active" data-size="14" title="İnce">İnce</button>
                        <button type="button" class="online-exp-trace-brush" data-size="22" title="Orta">Orta</button>
                        <button type="button" class="online-exp-trace-brush" data-size="30" title="Kalın">Kalın</button>
                    </div>
                    <div class="online-exp-trace-ring" aria-hidden="true">
                        <svg class="online-exp-trace-ring__svg" viewBox="0 0 44 44">
                            <circle cx="22" cy="22" r="18" class="online-exp-trace-ring__bg"/>
                            <circle cx="22" cy="22" r="18" class="online-exp-trace-ring__fill" id="exp-trace-ring-fill"/>
                        </svg>
                        <span class="online-exp-trace-ring__pct" id="exp-trace-ring-pct">0%</span>
                    </div>
                    <button type="button" class="online-exp-trace-clear-btn" id="exp-trace-clear">↺ Temizle</button>
                </div>

                <div class="online-exp-trace-sheet">
                    <div class="online-exp-trace-sheet__corner" aria-hidden="true"></div>
                    <div class="online-exp-trace-canvas-wrap" id="exp-trace-canvas-wrap">
                        <canvas id="exp-trace-canvas" class="online-exp-trace-canvas" aria-label="Çizim alanı"></canvas>
                        <div class="online-exp-trace-celebrate" id="exp-trace-celebrate" hidden aria-hidden="true">
                            <div class="online-exp-trace-celebrate__inner">
                                <span class="online-exp-trace-celebrate__emoji">⭐</span>
                                <p class="online-exp-trace-celebrate__title">Harika!</p>
                                <p class="online-exp-trace-celebrate__sub">Çizgiyi başarıyla tamamladın</p>
                            </div>
                        </div>
                    </div>
                    <p class="online-exp-trace-progress" id="exp-trace-progress">
                        <span class="online-exp-trace-progress__dot online-exp-trace-progress__dot--start" aria-hidden="true"></span>
                        Yeşil noktadan başla, çizgiyi takip et
                        <span class="online-exp-trace-progress__dot online-exp-trace-progress__dot--end" aria-hidden="true"></span>
                    </p>
                </div>
            </div>
        </div>

        <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
        <button type="button" class="btn-primary online-exp-trace-start-btn" id="exp-trace-start" hidden>Çizmeye başla ✏️</button>
    </div>
</main>

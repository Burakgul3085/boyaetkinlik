<main class="online-exp-stage" aria-label="Çizgi tamamlama">
    <p class="online-exp-stage-label">Desen seç · çizginin üzerinden fare veya parmağınla geç</p>
    <div class="online-exp-stage__inner" id="exp-stage-inner">
        <div class="online-exp-trace-patterns" id="exp-trace-patterns">
            <button type="button" class="online-exp-trace-pattern-btn online-exp-trace-pattern-btn--active" data-pattern="ev">🏠 Ev</button>
            <button type="button" class="online-exp-trace-pattern-btn" data-pattern="kelebek">🦋 Kelebek</button>
            <button type="button" class="online-exp-trace-pattern-btn" data-pattern="dalga">〰️ Dalga</button>
        </div>
        <div class="online-exp-trace-canvas-wrap">
            <canvas id="exp-trace-canvas" class="online-exp-trace-canvas" width="480" height="300" aria-label="Çizim alanı"></canvas>
            <p class="online-exp-trace-progress" id="exp-trace-progress">Hazır — çizgiyi takip et</p>
        </div>
        <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
        <button type="button" class="btn-secondary text-sm" id="exp-trace-clear" hidden>Temizle</button>
    </div>
</main>

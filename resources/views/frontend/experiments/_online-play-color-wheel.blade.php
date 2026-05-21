<main class="online-exp-stage" aria-label="Renk çarkı">
    <p class="online-exp-stage-label">Ana renkleri seç · ara renkleri keşfet</p>
    <div class="online-exp-stage__inner online-exp-stage__inner--wheel" id="exp-stage-inner">
        <div class="online-exp-wheel-wrap" id="exp-wheel-wrap">
            <svg class="online-exp-wheel-svg" id="exp-wheel-svg" viewBox="0 0 320 320" aria-label="Renk çarkı">
                <circle cx="160" cy="160" r="150" fill="#faf5ff" stroke="#e9d5ff" stroke-width="4"/>
                <g id="exp-wheel-segments"></g>
                <circle cx="160" cy="160" r="36" fill="white" stroke="#c4b5fd" stroke-width="2"/>
                <text x="160" y="165" text-anchor="middle" class="online-exp-wheel-center-text" id="exp-wheel-center-text">Ana renk</text>
            </svg>
        </div>
        <div class="online-exp-wheel-controls" id="exp-wheel-controls">
            <button type="button" class="online-exp-wheel-btn" data-primary="red" style="--c:#ef4444">Kırmızı</button>
            <button type="button" class="online-exp-wheel-btn" data-primary="yellow" style="--c:#eab308">Sarı</button>
            <button type="button" class="online-exp-wheel-btn" data-primary="blue" style="--c:#3b82f6">Mavi</button>
        </div>
        <button type="button" class="btn-primary online-exp-wheel-reveal" id="exp-wheel-reveal" hidden>Ara renkleri göster ✨</button>
        <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
    </div>
</main>

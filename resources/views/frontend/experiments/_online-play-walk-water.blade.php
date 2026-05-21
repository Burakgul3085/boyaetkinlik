<main class="online-exp-stage" aria-label="3D deney sahnesi">
    <p class="online-exp-stage-label">3D deney masası — tıklayarak kur, sonra animasyonu izle</p>
    <div class="online-exp-stage__inner" id="exp-stage-inner">
        <div class="online-exp-palette" id="exp-palette" hidden>
            <p class="online-exp-palette-label">① Paletten veya özel renkten seç · ② Parlayan 1, 3, 5, 7 bardaklara tıkla</p>
            <div class="online-exp-palette-colors" id="exp-palette-colors"></div>
            <label class="online-exp-custom-color">
                <span>Özel renk</span>
                <input type="color" id="exp-color-picker" value="#e11d48" title="İstediğin rengi seç">
            </label>
        </div>
        <div class="online-exp-3d-arena" id="exp-3d-arena">
            <div class="online-exp-3d-arena__glow" aria-hidden="true"></div>
            <div class="online-exp-3d-arena__shadow" aria-hidden="true"></div>
            <div class="online-exp-3d-world" id="exp-3d-world">
                <div class="online-exp-3d-row" id="exp-cups-row"></div>
            </div>
        </div>
        <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
        <button type="button" class="btn-primary online-exp-start-btn" id="exp-btn-start" hidden>
            Deneyi başlat — karışımları izle ✨
        </button>
    </div>
</main>

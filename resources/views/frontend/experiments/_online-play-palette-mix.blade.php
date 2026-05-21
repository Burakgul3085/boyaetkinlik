<main class="online-exp-stage" aria-label="Boya paleti karışım sahnesi">
    <p class="online-exp-stage-label">Boya paleti — iki rengi seç, karışımını keşfet</p>
    <div class="online-exp-stage__inner" id="exp-stage-inner">
        <div class="online-exp-mix-palette-bar" id="exp-mix-palette-bar" hidden>
            <p class="online-exp-palette-label" id="exp-mix-palette-hint">Bir renk seç</p>
            <div class="online-exp-palette-colors" id="exp-mix-palette-colors"></div>
            <label class="online-exp-custom-color">
                <span>Özel renk</span>
                <input type="color" id="exp-mix-color-picker" value="#3b82f6" title="Özel renk">
            </label>
        </div>

        <div class="online-exp-mix-lab" id="exp-mix-lab">
            <div class="online-exp-mix-slot" id="exp-mix-slot-a" data-slot="a">
                <p class="online-exp-mix-slot__label">1. renk</p>
                <button type="button" class="online-exp-mix-slot__btn" id="exp-mix-btn-a" aria-label="Birinci rengi seç">
                    <span class="online-exp-mix-slot__circle" id="exp-mix-circle-a"></span>
                    <span class="online-exp-mix-slot__name" id="exp-mix-name-a">Seç</span>
                </button>
            </div>

            <div class="online-exp-mix-bowl-wrap">
                <div class="online-exp-mix-bowl" id="exp-mix-bowl">
                    <div class="online-exp-mix-bowl__inner" id="exp-mix-result"></div>
                    <div class="online-exp-mix-bowl__swirl" id="exp-mix-swirl" hidden aria-hidden="true"></div>
                </div>
                <p class="online-exp-mix-result-label" id="exp-mix-result-label">Karışım burada görünür</p>
                <button type="button" class="btn-primary online-exp-mix-stir-btn" id="exp-mix-stir" hidden>
                    Karıştır 🎨
                </button>
            </div>

            <div class="online-exp-mix-slot" id="exp-mix-slot-b" data-slot="b">
                <p class="online-exp-mix-slot__label">2. renk</p>
                <button type="button" class="online-exp-mix-slot__btn" id="exp-mix-btn-b" aria-label="İkinci rengi seç">
                    <span class="online-exp-mix-slot__circle" id="exp-mix-circle-b"></span>
                    <span class="online-exp-mix-slot__name" id="exp-mix-name-b">Seç</span>
                </button>
            </div>
        </div>

        <p class="online-exp-stage-hint" id="exp-stage-hint"></p>
    </div>
</main>

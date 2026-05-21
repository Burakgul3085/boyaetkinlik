@php
    use App\Support\OnlineExperimentLab;
    $traceVariant = OnlineExperimentLab::traceStudioVariant($labType ?? '');
    $traceLabels = [
        'shape' => ['label' => 'Çizgi çalışması — kalemin çizginin üzerinden yavaşça geçsin', 'start' => 'Çizmeye başla ✏️'],
        'letter' => ['label' => 'Harf çizgi stüdyosu — büyük harf yolunu takip et', 'start' => 'Harfi çizmeye başla 🔤'],
        'number' => ['label' => 'Sayı çizgi stüdyosu — rakam yolunu takip et', 'start' => 'Sayıyı çizmeye başla 🔢'],
    ];
    $labels = $traceLabels[$traceVariant] ?? $traceLabels['shape'];
@endphp
<main class="online-exp-stage online-exp-stage--trace online-exp-stage--trace-{{ $traceVariant }}" aria-label="{{ $labels['label'] }}">
    <p class="online-exp-stage-label">{{ $labels['label'] }}</p>
    <div class="online-exp-stage__inner online-exp-stage__inner--trace" id="exp-stage-inner">
        <div class="online-exp-trace-studio" id="exp-trace-studio">
            <div
                class="online-exp-trace-patterns online-exp-trace-patterns--{{ $traceVariant }}"
                id="exp-trace-patterns"
                role="listbox"
                aria-label="{{ $traceVariant === 'letter' ? 'Harf seç' : ($traceVariant === 'number' ? 'Sayı seç' : 'Desen seç') }}"
            ></div>

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
                                <span class="online-exp-trace-celebrate__emoji" id="exp-trace-celebrate-emoji">⭐</span>
                                <p class="online-exp-trace-celebrate__title" id="exp-trace-celebrate-title">Harika!</p>
                                <p class="online-exp-trace-celebrate__sub" id="exp-trace-celebrate-sub">Çizgiyi başarıyla tamamladın</p>
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
        <button type="button" class="btn-primary online-exp-trace-start-btn" id="exp-trace-start" hidden>{{ $labels['start'] }}</button>
    </div>
</main>

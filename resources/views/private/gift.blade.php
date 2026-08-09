<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="googlebot" content="noindex, nofollow, noarchive, nosnippet">
    <meta name="referrer" content="no-referrer">
    <meta name="theme-color" content="#06080D">
    <title>{{ $pageTitle }}</title>
    @vite(['resources/css/private-gift.css', 'resources/js/private-gift.js'])
</head>
<body class="pg-body">
    <div class="pg-preloader" id="pg-preloader" aria-hidden="true">
        <p class="pg-preloader__text">bir şey hazırlanıyor…</p>
        <span class="pg-preloader__pulse"></span>
    </div>

    <div class="pg-progress" aria-hidden="true">
        <div class="pg-progress__bar" id="pg-progress-bar"></div>
    </div>

    <canvas class="pg-particles" id="pg-particles" aria-hidden="true"></canvas>
    <div class="pg-ambient" aria-hidden="true"></div>

    <main class="pg-main" id="pg-main">
        {{-- Sahne 1 --}}
        <section class="pg-scene pg-scene--hero is-active" id="pg-scene-1" data-pg-scene>
            <div class="pg-scene__inner pg-scene__inner--center">
                <p class="pg-eyebrow">özel bir yer</p>
                <h1 class="pg-title">Bu sıradan bir web sitesi değil.</h1>
                <p class="pg-lead">Sadece bugün biraz olsun gülümsemen için hazırlanmış küçük bir yer.</p>
                <p class="pg-soft">Hazırsan başlayalım.</p>
                <button type="button" class="pg-btn" id="pg-start" data-pg-next="#pg-scene-2">
                    Devam et
                </button>
            </div>
        </section>

        {{-- Sahne 2 --}}
        <section class="pg-scene" id="pg-scene-2" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Bugün nasılsın?</h2>
                <p class="pg-lead">Gerçek cevabı seçebilirsin.</p>
                <div class="pg-choices" id="pg-mood-choices" role="group" aria-label="Bugün nasılsın">
                    <button type="button" class="pg-choice" data-mood="tired">Biraz yorgunum</button>
                    <button type="button" class="pg-choice" data-mood="confused">Kafam biraz karışık</button>
                    <button type="button" class="pg-choice" data-mood="ok">İdare ediyorum</button>
                    <button type="button" class="pg-choice" data-mood="better">Bugün biraz daha iyiyim</button>
                </div>
                <div class="pg-mood-reply" id="pg-mood-reply" hidden aria-live="polite"></div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-3" id="pg-mood-continue" hidden>
                    Devam
                </button>
            </div>
        </section>

        {{-- Sahne 3 --}}
        <section class="pg-scene" id="pg-scene-3" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <p class="pg-eyebrow">küçük bir dokunuş</p>
                <div class="pg-terminal" role="region" aria-label="Kısa not terminali">
                    <div class="pg-terminal__bar">
                        <span class="pg-terminal__dot"></span>
                        <span class="pg-terminal__dot"></span>
                        <span class="pg-terminal__dot"></span>
                        <span class="pg-terminal__name">a-small-note.sh</span>
                    </div>
                    <pre class="pg-terminal__body" id="pg-terminal-body" aria-live="polite"></pre>
                </div>
                <p class="pg-soft pg-mt">Evet, buraya kadar gerçekten kod yazıldı.<br>Biraz gereksiz olabilir. Ama bazen güzel şeyler biraz gereksiz emek ister.</p>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-4">Devam</button>
            </div>
        </section>

        {{-- Sahne 4 --}}
        <section class="pg-scene" id="pg-scene-4" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Sende fark ettiğim birkaç güzel şey var.</h2>
                <p class="pg-lead">Belki sen her zaman fark etmiyorsundur.</p>
                <div class="pg-cards">
                    <article class="pg-card">
                        <span class="pg-card__num">01</span>
                        <h3 class="pg-card__title">İçtenliğin</h3>
                        <p class="pg-card__text">Bir şey anlatırken gerçekten kendin olman güzel. İnsan bunu fark ediyor.</p>
                    </article>
                    <article class="pg-card">
                        <span class="pg-card__num">02</span>
                        <h3 class="pg-card__title">Güçlü kalmaya çalışman</h3>
                        <p class="pg-card__text">Her şey her zaman kolay olmayabilir ama yine de devam eden tarafını görmek güzel.</p>
                    </article>
                    <article class="pg-card">
                        <span class="pg-card__num">03</span>
                        <h3 class="pg-card__title">Gülüşün</h3>
                        <p class="pg-card__text">Bazı insanların gülüşü sadece kendi yüzünü değil, karşısındaki insanın gününü de değiştiriyor.</p>
                    </article>
                    <article class="pg-card">
                        <span class="pg-card__num">04</span>
                        <h3 class="pg-card__title">İnceliğin</h3>
                        <p class="pg-card__text">Küçük şeylere verdiğin değer, aslında senin hakkında çok şey söylüyor.</p>
                    </article>
                    <article class="pg-card">
                        <span class="pg-card__num">05</span>
                        <h3 class="pg-card__title">Kalbin</h3>
                        <p class="pg-card__text">İnsan bazen kelimelerden çok davranışlardan anlaşılıyor.</p>
                    </article>
                </div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-5">Devam</button>
            </div>
        </section>

        {{-- Sahne 5 --}}
        <section class="pg-scene" id="pg-scene-5" data-pg-scene hidden>
            <div class="pg-scene__inner pg-scene__inner--center">
                <h2 class="pg-title pg-title--md">Bazen günler biraz zor geçer</h2>
                <button type="button" class="pg-btn pg-btn--wide" id="pg-hard-day">
                    Bugün biraz zor geçtiyse dokun.
                </button>
                <div class="pg-hard-reply" id="pg-hard-reply" aria-live="polite"></div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-6">Devam</button>
            </div>
        </section>

        {{-- Sahne 6 --}}
        <section class="pg-scene" id="pg-scene-6" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Bugün sana bırakmak istediğim bir şarkı var.</h2>
                <p class="pg-lead">Bazı şarkılar söylemek istediğimiz şeyleri bizden biraz daha güzel söylüyor.</p>

                @php
                    $primary = $music['primary'] ?? [];
                    $secondary = $music['secondary'] ?? [];
                    $pUrl = trim((string) ($primary['url'] ?? ''));
                    $sUrl = trim((string) ($secondary['url'] ?? ''));
                    $pArtist = trim((string) ($primary['artist'] ?? ''));
                    $pTitle = trim((string) ($primary['title'] ?? ''));
                    $sArtist = trim((string) ($secondary['artist'] ?? ''));
                    $sTitle = trim((string) ($secondary['title'] ?? ''));
                    $ytId = $youtube['id'] ?? null;
                    $ytStart = (int) ($youtube['start'] ?? 102);
                    $ytEmbed = $youtube['embed_url'] ?? null;
                @endphp

                <div class="pg-tracks">
                    <article class="pg-track pg-track--player">
                        <div class="pg-track__art" aria-hidden="true">♪</div>
                        <div class="pg-track__meta">
                            <p class="pg-track__artist">{{ $pArtist !== '' ? $pArtist : 'Gripin' }}</p>
                            <h3 class="pg-track__title">{{ $pTitle !== '' ? $pTitle : 'Nasip' }}</h3>
                            <p class="pg-soft">1:42’den başlayacak — sahne açılınca çalar.</p>
                        </div>

                        @if($ytEmbed)
                            <div
                                class="pg-yt"
                                id="pg-yt"
                                data-embed-url="{{ $ytEmbed }}"
                                data-yt-id="{{ $ytId }}"
                                data-yt-start="{{ $ytStart }}"
                            >
                                <div class="pg-yt__frame-wrap" id="pg-yt-host">
                                    {{-- iframe JS ile eklenir (autoplay + kullanıcı jesti) --}}
                                </div>
                                <button type="button" class="pg-btn pg-btn--small" id="pg-yt-play" hidden>
                                    Müziği başlat
                                </button>
                            </div>
                        @elseif($pUrl !== '')
                            <a class="pg-btn pg-btn--small" href="{{ $pUrl }}" target="_blank" rel="noopener noreferrer">Şarkıyı dinle</a>
                        @else
                            <p class="pg-soft">Bağlantı henüz eklenmedi.</p>
                        @endif
                    </article>

                    @if($sTitle !== '' || $sUrl !== '')
                    <article class="pg-track">
                        <div class="pg-track__art" aria-hidden="true">♪</div>
                        <div class="pg-track__meta">
                            <p class="pg-track__artist">{{ $sArtist !== '' ? $sArtist : ' ' }}</p>
                            <h3 class="pg-track__title">{{ $sTitle !== '' ? $sTitle : 'Yazdı Kâtip' }}</h3>
                            <p class="pg-soft">Biri biraz kalbe, biri biraz yüzüne iyi gelsin diye.</p>
                        </div>
                        @if($sUrl !== '')
                            <a class="pg-btn pg-btn--small" href="{{ $sUrl }}" target="_blank" rel="noopener noreferrer">Şarkıyı dinle</a>
                        @endif
                    </article>
                    @endif
                </div>

                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-7">Devam</button>
            </div>
        </section>

        {{-- Sahne 7 --}}
        <section class="pg-scene" id="pg-scene-7" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Belki bir gün…</h2>
                <ul class="pg-maybe">
                    <li>Bir kahve içeriz.</li>
                    <li>Bir akşam yürüyüşe çıkarız.</li>
                    <li>Berbat bir filmi izleyip birlikte eleştiririz.</li>
                    <li>Yeni bir yer keşfederiz.</li>
                    <li>Plansız bir gün geçiririz.</li>
                    <li>Sadece oturup uzun uzun konuşuruz.</li>
                </ul>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-8">Devam</button>
            </div>
        </section>

        {{-- Sahne 8 — foto --}}
        <section class="pg-scene" id="pg-scene-8" data-pg-scene hidden>
            <div class="pg-scene__inner pg-scene__inner--split">
                <div class="pg-reveal-copy">
                    <p class="pg-lead">Bu küçük şeyi kimin hazırladığını merak edersen…</p>
                </div>
                <div class="pg-portrait" data-pg-reveal>
                    <div class="pg-portrait__glow" aria-hidden="true"></div>
                    @if(!empty($photoUrl))
                        <img
                            class="pg-portrait__img"
                            src="{{ $photoUrl }}"
                            alt="{{ $sender }}"
                            width="480"
                            height="600"
                            loading="lazy"
                            decoding="async"
                        >
                    @else
                        <div class="pg-portrait__placeholder" role="img" aria-label="{{ $sender }}">
                            <span>{{ mb_substr($sender, 0, 1) }}</span>
                        </div>
                    @endif
                    <div class="pg-portrait__caption">
                        <h2 class="pg-title pg-title--sm">Ekranın arkasındaki kişi: {{ $sender }}</h2>
                        <p class="pg-lead">Bu kişi senin için yaptı.</p>
                        <p class="pg-lead">Biraz gülümse diye.</p>
                        <p class="pg-soft pg-soft--late">Ve evet, her detayını düşünerek.</p>
                    </div>
                </div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-9">Son bir şey</button>
            </div>
        </section>

        {{-- Sahne 9 — final + flower --}}
        <section class="pg-scene pg-scene--final" id="pg-scene-9" data-pg-scene hidden>
            <div class="pg-scene__inner pg-scene__inner--center">
                <div class="pg-final-lines" id="pg-final-lines" aria-live="polite">
                    <p class="pg-final-line" data-step="0">Son bir şey daha…</p>
                    <p class="pg-final-line" data-step="1" hidden>Sana küçük bir şey bırakmak istedim.</p>
                    <p class="pg-final-line" data-step="2" hidden>Gerçek değil belki…</p>
                    <p class="pg-final-line" data-step="3" hidden>Ama içindeki düşünce gerçek.</p>
                    <h2 class="pg-title pg-final-line" data-step="4" hidden>Burak'tan sana küçük bir hediye.</h2>
                </div>

                <div class="pg-flower-wrap" id="pg-flower-wrap" hidden>
                    <svg class="pg-flower" id="pg-flower" viewBox="0 0 200 280" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <defs>
                            <radialGradient id="pg-petal-g" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" stop-color="#F5EBD6"/>
                                <stop offset="70%" stop-color="#C9AD7A"/>
                                <stop offset="100%" stop-color="#8A7350"/>
                            </radialGradient>
                            <filter id="pg-glow" x="-40%" y="-40%" width="180%" height="180%">
                                <feGaussianBlur stdDeviation="3" result="b"/>
                                <feMerge>
                                    <feMergeNode in="b"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>
                        <g class="pg-flower__stem-group">
                            <path class="pg-flower__stem" d="M100 250 C98 200 102 160 100 120" fill="none" stroke="#5C7A62" stroke-width="3" stroke-linecap="round"/>
                            <path class="pg-flower__leaf pg-flower__leaf--l" d="M100 170 C70 160 55 140 62 125 C80 130 95 145 100 160" fill="#6E8F78"/>
                            <path class="pg-flower__leaf pg-flower__leaf--r" d="M100 190 C130 180 145 160 138 145 C120 150 105 165 100 180" fill="#7A9982"/>
                        </g>
                        <g class="pg-flower__head" filter="url(#pg-glow)">
                            <ellipse class="pg-flower__petal" data-i="0" cx="100" cy="78" rx="18" ry="34" fill="url(#pg-petal-g)"/>
                            <ellipse class="pg-flower__petal" data-i="1" cx="100" cy="78" rx="18" ry="34" fill="url(#pg-petal-g)"/>
                            <ellipse class="pg-flower__petal" data-i="2" cx="100" cy="78" rx="18" ry="34" fill="url(#pg-petal-g)"/>
                            <ellipse class="pg-flower__petal" data-i="3" cx="100" cy="78" rx="18" ry="34" fill="url(#pg-petal-g)"/>
                            <ellipse class="pg-flower__petal" data-i="4" cx="100" cy="78" rx="18" ry="34" fill="url(#pg-petal-g)"/>
                            <ellipse class="pg-flower__petal" data-i="5" cx="100" cy="78" rx="18" ry="34" fill="url(#pg-petal-g)"/>
                            <circle class="pg-flower__center" cx="100" cy="90" r="16" fill="#E8D5A8"/>
                        </g>
                    </svg>
                </div>

                <div class="pg-after-flower" id="pg-after-flower" hidden>
                    <p class="pg-lead">Belki bu gerçek bir çiçek değil.</p>
                    <p class="pg-lead">Ama içindeki düşünce gerçek.</p>
                    <p class="pg-soft">Küçük bir gülümsemeye sebep olursa, amacı tamamlanmış sayılır.</p>
                    <p class="pg-signature">Burak'tan sana.<br><span>— B.</span></p>
                </div>

                <div class="pg-mission" id="pg-mission">
                    <button type="button" class="pg-btn" id="pg-smile-btn">Gülümsedim :)</button>
                    <div class="pg-mission__done" id="pg-mission-done" hidden>
                        <p class="pg-lead">Tamam.</p>
                        <p class="pg-soft" id="pg-mission-text" hidden>O zaman bu sayfanın görevi başarıyla tamamlandı.</p>
                        <pre class="pg-code-line" id="pg-mission-code" hidden>mission.status = "completed";</pre>
                    </div>
                </div>

                <footer class="pg-closing" id="pg-closing" hidden>
                    <p>Senden bir cevap beklemek için değil, seni önemsediğimi hissettirmek için.</p>
                    <p class="pg-closing__last">Şimdilik sadece biraz gülümse.</p>
                </footer>
            </div>
        </section>
    </main>

    <button type="button" class="pg-easter" id="pg-easter" aria-label="Geliştirici notu">
        &lt;/heart&gt;
    </button>

    <div class="pg-modal" id="pg-modal" hidden role="dialog" aria-modal="true" aria-labelledby="pg-modal-title">
        <div class="pg-modal__backdrop" data-pg-modal-close></div>
        <div class="pg-modal__panel">
            <h2 class="pg-modal__title" id="pg-modal-title">Developer Note</h2>
            <p>Bu sayfayı yapmak birkaç saat sürebilir.</p>
            <p>Seni düşünmek biraz daha uzun sürdü.</p>
            <button type="button" class="pg-btn pg-btn--small" data-pg-modal-close>Kapat</button>
        </div>
    </div>
</body>
</html>

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
        <p class="pg-preloader__text">senin için bir şey hazırlanıyor…</p>
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
                <p class="pg-eyebrow pg-anim" data-d="0">sadece sana</p>
                <h1 class="pg-title pg-anim" data-d="1">Bu sıradan bir web sitesi değil.</h1>
                <p class="pg-lead pg-anim" data-d="2">
                    Kimseye gösterilmek için değil. Satış için değil.
                    Sadece bugün, yüzünde küçük bir gülümseme kalsın diye
                    hazırlanmış sessiz bir yer.
                </p>
                <p class="pg-soft pg-anim" data-d="3">
                    Acele etme. İstersen yavaş yavaş oku.
                    Hazır olduğunda başlayalım.
                </p>
                <button type="button" class="pg-btn pg-anim" data-d="4" id="pg-start" data-pg-next="#pg-scene-2">
                    Başlayalım
                </button>
            </div>
        </section>

        {{-- Sahne 2 --}}
        <section class="pg-scene" id="pg-scene-2" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Önce bir şey sorayım…</h2>
                <p class="pg-lead">
                    Bugün nasılsın? Tek bir “iyi” demen zorunlu değil.
                    Gerçekten hissettiğin yere en yakın olanı seçebilirsin.
                </p>
                <p class="pg-hint" role="note">
                    <span class="pg-hint__dot" aria-hidden="true"></span>
                    Aşağıdaki cevaplardan birine bas — sonra mesajım gelecek.
                </p>
                <div class="pg-choices" id="pg-mood-choices" role="group" aria-label="Bugün nasılsın">
                    <button type="button" class="pg-choice" data-mood="tired">Biraz yorgunum</button>
                    <button type="button" class="pg-choice" data-mood="confused">Kafam biraz karışık</button>
                    <button type="button" class="pg-choice" data-mood="ok">İdare ediyorum</button>
                    <button type="button" class="pg-choice" data-mood="better">Bugün biraz daha iyiyim</button>
                </div>
                <div class="pg-mood-reply" id="pg-mood-reply" hidden aria-live="polite"></div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-3" id="pg-mood-continue" hidden>
                    Devam et
                </button>
            </div>
        </section>

        {{-- Sahne 3 --}}
        <section class="pg-scene" id="pg-scene-3" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <p class="pg-eyebrow">küçük bir dokunuş</p>
                <h2 class="pg-title pg-title--md">Birisi senin için gerçekten oturup yazdı.</h2>
                <p class="pg-lead">
                    Evet, burası kod. Ama asıl mesele o değil.
                    Asıl mesele… bunu sen aklına gelince, sen iyi hissetsin diye yapmak.
                </p>
                <div class="pg-terminal" role="region" aria-label="Kısa not terminali">
                    <div class="pg-terminal__bar">
                        <span class="pg-terminal__dot"></span>
                        <span class="pg-terminal__dot"></span>
                        <span class="pg-terminal__dot"></span>
                        <span class="pg-terminal__name">a-small-note.sh</span>
                    </div>
                    <pre class="pg-terminal__body" id="pg-terminal-body" aria-live="polite"></pre>
                </div>
                <p class="pg-soft pg-mt">
                    Biraz gereksiz emek gibi durabilir.
                    Ama bazen en tatlı şeyler, kimsenin istemediği o “gereksiz” emekten doğar.
                </p>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-4">Devam et</button>
            </div>
        </section>

        {{-- Sahne 4 --}}
        <section class="pg-scene" id="pg-scene-4" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Sende fark ettiğim birkaç güzel şey var.</h2>
                <p class="pg-lead">
                    Belki sen her gün bakınca fark etmiyorsundur.
                    Ama dışarıdan, sessizce bakıldığında bunlar çok net görünüyor.
                </p>
                <div class="pg-cards">
                    <article class="pg-card" style="--i:0">
                        <span class="pg-card__num">01</span>
                        <h3 class="pg-card__title">İçtenliğin</h3>
                        <p class="pg-card__text">
                            Bir şey anlatırken gerçekten kendin olman güzel.
                            İnsan savrulmadan, süslemeden konuştuğunu fark ediyor — ve güven veriyor.
                        </p>
                    </article>
                    <article class="pg-card" style="--i:1">
                        <span class="pg-card__num">02</span>
                        <h3 class="pg-card__title">Güçlü kalmaya çalışman</h3>
                        <p class="pg-card__text">
                            Her şey her zaman kolay olmayabilir.
                            Yine de “devam ediyorum” diyen o tarafın… fark edilmeye değer.
                        </p>
                    </article>
                    <article class="pg-card" style="--i:2">
                        <span class="pg-card__num">03</span>
                        <h3 class="pg-card__title">Gülüşün</h3>
                        <p class="pg-card__text">
                            Bazı insanların gülüşü sadece kendi yüzünü aydınlatır.
                            Senin gülüşün… karşı tarafa da bulaşır. Gününü yumuşatır.
                        </p>
                    </article>
                    <article class="pg-card" style="--i:3">
                        <span class="pg-card__num">04</span>
                        <h3 class="pg-card__title">İnceliğin</h3>
                        <p class="pg-card__text">
                            Küçük şeylere verdiğin değer, aslında senin kalbin hakkında çok şey söylüyor.
                            Büyük jestler değil; kibarlık, dikkat, incelik.
                        </p>
                    </article>
                    <article class="pg-card" style="--i:4">
                        <span class="pg-card__num">05</span>
                        <h3 class="pg-card__title">Kalbin</h3>
                        <p class="pg-card__text">
                            İnsan bazen kelimelerden çok davranışlardan anlaşılır.
                            Ve senin kalbin… sessiz de olsa hissediliyor.
                        </p>
                    </article>
                </div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-5">Devam et</button>
            </div>
        </section>

        {{-- Sahne 5 --}}
        <section class="pg-scene" id="pg-scene-5" data-pg-scene hidden>
            <div class="pg-scene__inner pg-scene__inner--center">
                <h2 class="pg-title pg-title--md">Bazen günler biraz zor geçer</h2>
                <p class="pg-lead">
                    Bunu yargılamak için söylemiyorum.
                    Sadece şunu hatırlatmak için: kötü bir gün, seni tanımlamaz.
                </p>
                <p class="pg-hint" role="note">
                    <span class="pg-hint__dot" aria-hidden="true"></span>
                    Aşağıdaki butona bas — her basışta sana bir not gelecek.
                </p>
                <button type="button" class="pg-btn pg-btn--wide" id="pg-hard-day">
                    Bugün biraz zor geçtiyse dokun.
                </button>
                <div class="pg-hard-reply" id="pg-hard-reply" hidden aria-live="polite"></div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-6">Devam et</button>
            </div>
        </section>

        {{-- Sahne 6 --}}
        <section class="pg-scene" id="pg-scene-6" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Bugün sana bırakmak istediğim bir şarkı var.</h2>
                <p class="pg-lead">
                    Bazı şarkılar, söylemek istediğimiz ama bir türlü tam oturtamadığımız cümleleri
                    bizden daha yumuşak söyler. Bu da onlardan biri.
                </p>

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
                            <p class="pg-soft">1:42’den başlayacak. İstersen kapatmadan dinle; istersen sadece eşlik etsin.</p>
                        </div>

                        @if($ytEmbed)
                            <div
                                class="pg-yt"
                                id="pg-yt"
                                data-embed-url="{{ $ytEmbed }}"
                                data-yt-id="{{ $ytId }}"
                                data-yt-start="{{ $ytStart }}"
                            >
                                <div class="pg-yt__frame-wrap" id="pg-yt-host"></div>
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

                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-7">Devam et</button>
            </div>
        </section>

        {{-- Sahne 7 --}}
        <section class="pg-scene" id="pg-scene-7" data-pg-scene hidden>
            <div class="pg-scene__inner">
                <h2 class="pg-title pg-title--md">Belki bir gün…</h2>
                <p class="pg-lead">
                    Bunlar söz değil. Söz verme de değil.
                    Sadece içinden geçen, hafif, samimi ihtimaller.
                </p>
                <ul class="pg-maybe">
                    <li style="--i:0">Belki bir gün bir kahve içeriz. Konuşmak zorunda bile değiliz; sessizlik de olur.</li>
                    <li style="--i:1">Belki bir akşam yürüyüşe çıkarız. Nereye gittiğimiz o kadar önemli olmaz.</li>
                    <li style="--i:2">Belki berbat bir filmi izler, birlikte eleştiririz. Gülmek yeter.</li>
                    <li style="--i:3">Belki yeni bir yer keşfederiz. Sıradan bir sokak bile özel görünebilir.</li>
                    <li style="--i:4">Belki plansız bir gün geçiririz. Her şeyin planlı olması gerekmez.</li>
                    <li style="--i:5">Belki sadece oturur, uzun uzun konuşuruz. Veya hiç konuşmayız; o da güzeldir.</li>
                </ul>
                <p class="pg-soft">Şimdilik bu kadarı… gerisi zamana kalır.</p>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-8">Devam et</button>
            </div>
        </section>

        {{-- Sahne 8 — foto --}}
        <section class="pg-scene" id="pg-scene-8" data-pg-scene hidden>
            <div class="pg-scene__inner pg-scene__inner--split">
                <div class="pg-reveal-copy">
                    <p class="pg-lead">
                        Bu küçük şeyi kimin hazırladığını merak edersen…
                        Merak etmek de çok doğal.
                    </p>
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
                        <p class="pg-soft">
                            Baskı kurmak için değil.
                            Senin iyi hissetmen, yüzünde küçük bir aydınlık kalsın diye.
                        </p>
                        <p class="pg-soft pg-soft--late">Ve evet… her detayını düşünerek.</p>
                    </div>
                </div>
                <button type="button" class="pg-btn pg-btn--ghost" data-pg-next="#pg-scene-9">Son bir şey kaldı</button>
            </div>
        </section>

        {{-- Sahne 9 — final + flower --}}
        <section class="pg-scene pg-scene--final" id="pg-scene-9" data-pg-scene hidden>
            <div class="pg-scene__inner pg-scene__inner--center">
                <div class="pg-final-lines" id="pg-final-lines" aria-live="polite">
                    <p class="pg-final-line" data-step="0">Son bir şey daha…</p>
                    <p class="pg-final-line" data-step="1" hidden>Sana küçük bir şey bırakmak istedim.</p>
                    <p class="pg-final-line" data-step="2" hidden>Gerçek değil belki…</p>
                    <p class="pg-final-line" data-step="3" hidden>Ama içindeki düşünce çok gerçek.</p>
                    <h2 class="pg-title pg-final-line" data-step="4" hidden>Burak'tan sana küçük bir hediye.</h2>
                </div>

                <p class="pg-soft" id="pg-flower-wait" hidden>Çiçek açılıyor… biraz bekle :)</p>

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
                    <p class="pg-soft">
                        Küçük bir gülümsemeye sebep olursa,
                        bugünün amacı tamamlanmış sayılır.
                    </p>
                    <p class="pg-signature">Burak'tan sana.<br><span>— B.</span></p>
                </div>

                <div class="pg-mission" id="pg-mission">
                    <p class="pg-hint pg-hint--center" id="pg-smile-hint" hidden>
                        <span class="pg-hint__dot" aria-hidden="true"></span>
                        Çiçek açıldıysa, istersen buraya bas.
                    </p>
                    <button type="button" class="pg-btn" id="pg-smile-btn" hidden disabled aria-disabled="true">
                        Gülümsedim :)
                    </button>
                    <div class="pg-mission__done" id="pg-mission-done" hidden>
                        <p class="pg-lead">Tamam.</p>
                        <p class="pg-soft" id="pg-mission-text" hidden>
                            O zaman bu sayfanın görevi başarıyla tamamlandı.
                            Yüzündeki o küçük gülümseme yeter.
                        </p>
                        <pre class="pg-code-line" id="pg-mission-code" hidden>mission.status = "completed";</pre>
                    </div>
                </div>

                <footer class="pg-closing" id="pg-closing" hidden>
                    <p>Senden bir cevap beklemek için değil…</p>
                    <p>Seni önemsediğimi, hissettirmek için.</p>
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
            <p style="margin-top:0.75rem;color:rgba(243,240,232,0.45);font-size:0.9rem;">
                Ve hayır — senden bir şey beklemiyor. Sadece gülümsemeni umuyor.
            </p>
            <button type="button" class="pg-btn pg-btn--small" data-pg-modal-close>Kapat</button>
        </div>
    </div>
</body>
</html>

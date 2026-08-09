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
                    Sadece senin için, sana özel hazırlandı.
                    Bugün yüzünde küçük bir gülümseme kalsın diye yapılmış sessiz bir yer.
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
                    Bunlar büyük vaatler değil.
                    Sadece aklına gelebilecek küçük, samimi ihtimaller.
                </p>
                <ul class="pg-maybe">
                    <li style="--i:0">Belki bir gün kısa bir kahve içilir.</li>
                    <li style="--i:1">Belki bir akşam kısa bir yürüyüş olur.</li>
                    <li style="--i:2">Belki güzel bir şey paylaşılır, gülünür.</li>
                    <li style="--i:3">Belki yeni bir yer denemek hoş olur.</li>
                    <li style="--i:4">Belki plansız, sakin bir gün olur.</li>
                    <li style="--i:5">Belki sadece kısa, rahat bir sohbet yeter.</li>
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
                        Bu küçük sayfayı kimin hazırladığını merak ediyorsan…
                    </p>
                </div>
                <div class="pg-portrait" data-pg-reveal>
                    <div class="pg-portrait__petals" aria-hidden="true">
                        <span></span><span></span><span></span><span></span>
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <div class="pg-portrait__glow" aria-hidden="true"></div>
                    <div class="pg-portrait__bloom" aria-hidden="true"></div>
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
                        <p class="pg-lead">Sadece senin için, sana özel yapıldı.</p>
                        <p class="pg-lead">Biraz gülümse diye.</p>
                        <p class="pg-soft">
                            Baskı yok. Beklenti yok.
                            Sadece küçük, tatlı bir jest.
                        </p>
                        <p class="pg-soft pg-soft--late">Her detayı sen aklına gelince düşünüldü.</p>
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
                    <div class="pg-flower-aura" aria-hidden="true"></div>
                    <svg class="pg-flower" id="pg-flower" viewBox="0 0 220 320" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
                        <defs>
                            <linearGradient id="pg-stem-g" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="#8FB896"/>
                                <stop offset="100%" stop-color="#4A6B52"/>
                            </linearGradient>
                            <radialGradient id="pg-petal-outer" cx="50%" cy="35%" r="65%">
                                <stop offset="0%" stop-color="#FFF6E4"/>
                                <stop offset="45%" stop-color="#E8C99A"/>
                                <stop offset="100%" stop-color="#B8925C"/>
                            </radialGradient>
                            <radialGradient id="pg-petal-mid" cx="50%" cy="40%" r="60%">
                                <stop offset="0%" stop-color="#FFF9EE"/>
                                <stop offset="55%" stop-color="#DDBF8A"/>
                                <stop offset="100%" stop-color="#C9A36A"/>
                            </radialGradient>
                            <radialGradient id="pg-petal-inner" cx="50%" cy="45%" r="55%">
                                <stop offset="0%" stop-color="#FFFDF8"/>
                                <stop offset="60%" stop-color="#F0D7A8"/>
                                <stop offset="100%" stop-color="#D4B07A"/>
                            </radialGradient>
                            <radialGradient id="pg-center-g" cx="50%" cy="50%" r="50%">
                                <stop offset="0%" stop-color="#FFF8E8"/>
                                <stop offset="70%" stop-color="#E8C98A"/>
                                <stop offset="100%" stop-color="#C4A06A"/>
                            </radialGradient>
                            <filter id="pg-glow" x="-50%" y="-50%" width="200%" height="200%">
                                <feGaussianBlur stdDeviation="2.5" result="b"/>
                                <feMerge>
                                    <feMergeNode in="b"/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                            <filter id="pg-soft-glow" x="-80%" y="-80%" width="260%" height="260%">
                                <feGaussianBlur stdDeviation="6" result="b"/>
                                <feColorMatrix in="b" type="matrix" values="1 0 0 0 0  0 0.9 0 0 0  0 0 0.6 0 0  0 0 0 0.45 0"/>
                                <feMerge>
                                    <feMergeNode/>
                                    <feMergeNode in="SourceGraphic"/>
                                </feMerge>
                            </filter>
                        </defs>

                        {{-- ışık çizgisi --}}
                        <path class="pg-flower__light" d="M110 290 L110 118" fill="none" stroke="rgba(201,173,122,0.35)" stroke-width="2" stroke-linecap="round"/>

                        <g class="pg-flower__stem-group">
                            <path class="pg-flower__stem" d="M110 290 C108 230 112 185 110 138" fill="none" stroke="url(#pg-stem-g)" stroke-width="3.5" stroke-linecap="round"/>
                            <path class="pg-flower__leaf pg-flower__leaf--l" d="M110 200 C78 188 55 168 58 148 C82 154 100 172 110 190" fill="#6E9478" opacity="0.95"/>
                            <path class="pg-flower__leaf pg-flower__leaf--r" d="M110 225 C142 212 162 190 158 168 C136 176 118 196 110 214" fill="#7FA68A" opacity="0.95"/>
                            <path class="pg-flower__leaf pg-flower__leaf--l2" d="M110 250 C85 242 68 228 70 212 C90 216 104 230 110 244" fill="#648A6E" opacity="0.9"/>
                        </g>

                        <g class="pg-flower__head" filter="url(#pg-soft-glow)" transform="translate(110 108)">
                            {{-- dış petaller --}}
                            <g class="pg-flower__ring pg-flower__ring--outer">
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="0" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="1" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(45)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="2" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(90)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="3" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(135)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="4" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(180)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="5" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(225)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="6" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(270)"/>
                                <ellipse class="pg-flower__petal" data-layer="o" data-i="7" cx="0" cy="-38" rx="16" ry="38" fill="url(#pg-petal-outer)" transform="rotate(315)"/>
                            </g>
                            {{-- orta --}}
                            <g class="pg-flower__ring pg-flower__ring--mid">
                                <ellipse class="pg-flower__petal" data-layer="m" data-i="0" cx="0" cy="-26" rx="13" ry="28" fill="url(#pg-petal-mid)"/>
                                <ellipse class="pg-flower__petal" data-layer="m" data-i="1" cx="0" cy="-26" rx="13" ry="28" fill="url(#pg-petal-mid)" transform="rotate(60)"/>
                                <ellipse class="pg-flower__petal" data-layer="m" data-i="2" cx="0" cy="-26" rx="13" ry="28" fill="url(#pg-petal-mid)" transform="rotate(120)"/>
                                <ellipse class="pg-flower__petal" data-layer="m" data-i="3" cx="0" cy="-26" rx="13" ry="28" fill="url(#pg-petal-mid)" transform="rotate(180)"/>
                                <ellipse class="pg-flower__petal" data-layer="m" data-i="4" cx="0" cy="-26" rx="13" ry="28" fill="url(#pg-petal-mid)" transform="rotate(240)"/>
                                <ellipse class="pg-flower__petal" data-layer="m" data-i="5" cx="0" cy="-26" rx="13" ry="28" fill="url(#pg-petal-mid)" transform="rotate(300)"/>
                            </g>
                            {{-- iç --}}
                            <g class="pg-flower__ring pg-flower__ring--inner">
                                <ellipse class="pg-flower__petal" data-layer="i" data-i="0" cx="0" cy="-16" rx="9" ry="18" fill="url(#pg-petal-inner)"/>
                                <ellipse class="pg-flower__petal" data-layer="i" data-i="1" cx="0" cy="-16" rx="9" ry="18" fill="url(#pg-petal-inner)" transform="rotate(72)"/>
                                <ellipse class="pg-flower__petal" data-layer="i" data-i="2" cx="0" cy="-16" rx="9" ry="18" fill="url(#pg-petal-inner)" transform="rotate(144)"/>
                                <ellipse class="pg-flower__petal" data-layer="i" data-i="3" cx="0" cy="-16" rx="9" ry="18" fill="url(#pg-petal-inner)" transform="rotate(216)"/>
                                <ellipse class="pg-flower__petal" data-layer="i" data-i="4" cx="0" cy="-16" rx="9" ry="18" fill="url(#pg-petal-inner)" transform="rotate(288)"/>
                            </g>
                            <circle class="pg-flower__center" cx="0" cy="0" r="12" fill="url(#pg-center-g)" filter="url(#pg-glow)"/>
                            <circle class="pg-flower__center-dot" cx="0" cy="0" r="4" fill="#FFF8E8" opacity="0.9"/>
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

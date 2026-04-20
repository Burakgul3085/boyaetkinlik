{{-- Sağ alt destek baloncuğu (Shopier); tıklanınca bulut bilgi kartı. Alt şerit reklamı varken alt boşluk artar. --}}
@php
    $supportShopierUrl = 'https://www.shopier.com/boyaetkinlik/46373754';
    $bottomPositionClass = ! empty($hasStickyFooterAd) && $hasStickyFooterAd
        ? 'bottom-[5.5rem] sm:bottom-[5.75rem]'
        : 'bottom-4 sm:bottom-5';
@endphp

<div
    id="kofi-support-widget"
    class="pointer-events-none fixed right-3 z-[38] sm:right-4 {{ $bottomPositionClass }}"
    style="padding-right: env(safe-area-inset-right, 0px); padding-bottom: env(safe-area-inset-bottom, 0px);"
    x-data="{ open: false }"
    @keydown.escape.window="open = false"
>
    <div
        class="pointer-events-auto relative flex max-w-[calc(100vw-1.5rem)] flex-col items-end gap-2"
        @click.outside="open = false"
    >
        {{-- Bulut / konuşma balonu --}}
        <div
            id="kofi-support-cloud"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-2 scale-95 opacity-0"
            x-transition:enter-end="translate-y-0 scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 scale-100 opacity-100"
            x-transition:leave-end="translate-y-2 scale-95 opacity-0"
            x-cloak
            class="kofi-cloud-panel relative w-[min(19rem,calc(100vw-2rem))] rounded-[1.75rem] border border-violet-100/90 bg-white/95 px-4 pb-4 pt-3 text-sm shadow-[0_16px_40px_rgba(76,29,149,0.14)] backdrop-blur-md dark:border-slate-600 dark:bg-slate-900/95 dark:shadow-[0_16px_40px_rgba(0,0,0,0.45)]"
            role="dialog"
            aria-modal="true"
            aria-labelledby="kofi-support-widget-title"
        >
            <div class="pointer-events-none absolute -bottom-2 right-8 h-4 w-4 rotate-45 rounded-sm border-b border-r border-violet-100/90 bg-white dark:border-slate-600 dark:bg-slate-900/95"></div>

            <div class="relative flex items-start justify-between gap-2">
                <p id="kofi-support-widget-title" class="pr-6 text-left text-[13px] leading-relaxed text-slate-700 dark:text-slate-200">
                    Web sayfamızın gelişimi için ya da bizi desteklemek için Shopier üzerinden bize maddi destek olabilirsiniz.
                </p>
                <button
                    type="button"
                    class="absolute right-0 top-0 inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-violet-200 bg-violet-50/90 text-sm font-semibold leading-none text-violet-600 shadow-sm transition hover:border-violet-300 hover:bg-violet-100 hover:text-violet-800 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300 dark:hover:border-slate-500 dark:hover:bg-slate-700"
                    @click="open = false"
                    aria-label="Bilgi kutusunu kapat"
                    title="Kapat"
                >
                    ×
                </button>
            </div>

            <a
                href="{{ $supportShopierUrl }}"
                target="_blank"
                rel="noopener noreferrer"
                class="relative mt-3 flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-violet-600 via-violet-600 to-fuchsia-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md shadow-violet-500/25 transition hover:brightness-105 focus:outline-none focus:ring-2 focus:ring-violet-300 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
            >
                <svg class="h-5 w-5 shrink-0 opacity-95" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M4 7.5h16l-1.2 9.6a2 2 0 0 1-2 1.76H7.2a2 2 0 0 1-2-1.76L4 7.5Z" stroke="currentColor" stroke-width="1.75" stroke-linejoin="round"/>
                    <path d="M9 11V5.5a3 3 0 0 1 6 0V11" stroke="currentColor" stroke-width="1.75" stroke-linecap="round"/>
                </svg>
                Shopier’de destek ol
            </a>
        </div>

        {{-- Baloncuk kümesi: en büyükte kahve görseli --}}
        <div class="relative h-[7.25rem] w-[7.25rem] shrink-0">
            <span class="kofi-deco-bubble pointer-events-none absolute right-1 top-2 h-3.5 w-3.5 rounded-full bg-violet-300/80 shadow-sm ring-2 ring-white/70 dark:bg-violet-500/50 dark:ring-slate-800/80"></span>
            <span class="kofi-deco-bubble kofi-deco-bubble--delay pointer-events-none absolute bottom-10 left-0 h-5 w-5 rounded-full bg-fuchsia-300/75 shadow-md ring-2 ring-white/80 dark:bg-fuchsia-500/40 dark:ring-slate-800/80"></span>

            <button
                type="button"
                class="kofi-bubble-main group absolute bottom-0 right-0 flex h-[4.5rem] w-[4.5rem] items-center justify-center overflow-hidden rounded-full border-4 border-white bg-gradient-to-br from-[#fdf6ee] via-[#fbeee4] to-[#f3dfd0] text-white shadow-[0_12px_28px_rgba(180,83,9,0.22)] ring-2 ring-amber-200/80 transition hover:scale-[1.04] hover:shadow-[0_16px_34px_rgba(180,83,9,0.28)] focus:outline-none focus:ring-2 focus:ring-amber-300 focus:ring-offset-2 dark:border-slate-800 dark:from-amber-950/40 dark:via-slate-800 dark:to-slate-900 dark:ring-amber-900/50 dark:focus:ring-offset-slate-900 sm:h-[5rem] sm:w-[5rem]"
                @click="open = !open"
                :aria-expanded="open ? 'true' : 'false'"
                aria-controls="kofi-support-cloud"
                title="Maddi destek"
            >
                <span class="sr-only">Destek bilgisini aç veya kapat</span>
                <img
                    src="{{ asset('images/support-bubble-cup.png') }}"
                    alt=""
                    width="120"
                    height="120"
                    class="h-[3.35rem] w-[3.35rem] select-none object-contain object-center drop-shadow-sm sm:h-[3.65rem] sm:w-[3.65rem]"
                    loading="lazy"
                    decoding="async"
                />
            </button>
        </div>
    </div>
</div>

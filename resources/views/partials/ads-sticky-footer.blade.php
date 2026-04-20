{{-- Sabit alt şerit: yalnızca ads_footer doluysa ve genel site yüzeyinde gösterilir (layouts.app). --}}
<div
    id="site-sticky-ad"
    class="pointer-events-auto fixed inset-x-0 bottom-0 z-30 h-[72px] border-t border-violet-100/70 bg-white/90 pb-[env(safe-area-inset-bottom,0px)] pt-1 shadow-[0_-8px_20px_rgba(76,29,149,0.10)] backdrop-blur-xl dark:border-slate-700 dark:bg-slate-950/88 dark:shadow-[0_-10px_24px_rgba(0,0,0,0.45)]"
    role="complementary"
    aria-label="Sponsor alanı"
>
    <div class="mx-auto flex h-full w-[min(92vw,820px)] items-center gap-1.5 px-2 pb-1 sm:px-3">
        <div class="flex h-[52px] flex-1 items-center rounded-xl border border-violet-100/80 bg-gradient-to-r from-white via-violet-50/20 to-white px-2 shadow-sm dark:border-slate-700 dark:bg-slate-900/90">
            <div class="sticky-ad-mini-slot w-full overflow-hidden text-center text-xs leading-tight text-slate-700 dark:text-slate-200 [&_*]:max-w-full">
                {!! $html !!}
            </div>
        </div>
        <button
            type="button"
            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-violet-200 bg-white text-sm font-medium leading-none text-violet-500 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500"
            data-sticky-ad-close
            aria-label="Alt reklamı kapat"
            title="Kapat"
        >
            ×
        </button>
    </div>
</div>
<style>
    #site-sticky-ad {
        transform: translateZ(0);
    }

    #site-sticky-ad .sticky-ad-mini-slot,
    #site-sticky-ad .sticky-ad-mini-slot > * {
        max-height: 46px !important;
        min-height: 0 !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot * {
        max-height: 46px !important;
        min-height: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot iframe,
    #site-sticky-ad .sticky-ad-mini-slot ins.adsbygoogle {
        height: 46px !important;
        min-height: 46px !important;
        max-height: 46px !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot ins.adsbygoogle[data-ad-status="unfilled"] {
        display: none !important;
    }

    /* Otomatik anchor reklam (2-3 sn sonra altta beliren ikinci bar) kapatılır. */
    body > ins.adsbygoogle[data-ad-format="anchor"],
    body > ins.adsbygoogle[data-anchor-status],
    body > .google-auto-placed {
        display: none !important;
        visibility: hidden !important;
    }
</style>
<script>
    (function () {
        var root = document.getElementById('site-sticky-ad');
        if (!root) return;
        try {
            if (sessionStorage.getItem('sticky-ad-dismissed') === '1') {
                root.remove();
                document.getElementById('site-main')?.classList.remove('pb-28', 'lg:pb-24');
                return;
            }
        } catch (e) {}
        var btn = root.querySelector('[data-sticky-ad-close]');
        if (!btn) return;

        var slot = root.querySelector('ins.adsbygoogle');

        function hideStickyAd() {
            root.remove();
            document.getElementById('site-main')?.classList.remove('pb-28', 'lg:pb-24');
        }

        // Reklam dolmazsa (unfilled) beyaz boş kutu görünmesin.
        function syncAdVisibility() {
            if (!slot) return;
            var status = slot.getAttribute('data-ad-status');
            if (status === 'unfilled') {
                hideStickyAd();
            }
        }

        if (slot && 'MutationObserver' in window) {
            var observer = new MutationObserver(syncAdVisibility);
            observer.observe(slot, { attributes: true, attributeFilter: ['data-ad-status'] });
            setTimeout(syncAdVisibility, 3500);
        }

        btn.addEventListener('click', function () {
            try {
                sessionStorage.setItem('sticky-ad-dismissed', '1');
            } catch (e) {}
            hideStickyAd();
        });
    })();
</script>

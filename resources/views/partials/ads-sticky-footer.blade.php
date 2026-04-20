{{-- Sabit alt şerit: yalnızca ads_footer doluysa ve genel site yüzeyinde gösterilir (layouts.app). --}}
<div
    id="site-sticky-ad"
    class="pointer-events-auto fixed inset-x-0 bottom-0 z-30 h-[76px] border-t border-violet-100/70 bg-white/90 pb-[env(safe-area-inset-bottom,0px)] pt-1.5 shadow-[0_-10px_24px_rgba(76,29,149,0.12)] backdrop-blur-xl dark:border-slate-700 dark:bg-slate-950/88 dark:shadow-[0_-10px_26px_rgba(0,0,0,0.45)]"
    role="complementary"
    aria-label="Sponsor alanı"
>
    <div class="mx-auto flex h-full w-[min(94vw,920px)] items-center gap-2 px-2 pb-1.5 sm:px-3">
        <div class="flex h-[56px] flex-1 items-center rounded-xl border border-violet-100/80 bg-gradient-to-r from-white via-violet-50/20 to-white px-2.5 shadow-sm dark:border-slate-700 dark:bg-slate-900/90">
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
    #site-sticky-ad .sticky-ad-mini-slot,
    #site-sticky-ad .sticky-ad-mini-slot > * {
        max-height: 50px !important;
        min-height: 0 !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot * {
        max-height: 50px !important;
        min-height: 0 !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot iframe,
    #site-sticky-ad .sticky-ad-mini-slot ins.adsbygoogle {
        height: 50px !important;
        min-height: 50px !important;
        max-height: 50px !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot ins.adsbygoogle[data-ad-status="unfilled"] {
        display: none !important;
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
        btn.addEventListener('click', function () {
            try {
                sessionStorage.setItem('sticky-ad-dismissed', '1');
            } catch (e) {}
            root.remove();
            document.getElementById('site-main')?.classList.remove('pb-28', 'lg:pb-24');
        });
    })();
</script>

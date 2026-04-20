{{-- Sabit alt şerit: yalnızca ads_footer doluysa ve genel site yüzeyinde gösterilir (layouts.app). --}}
<div
    id="site-sticky-ad"
    class="pointer-events-auto fixed inset-x-0 bottom-0 z-30 border-t border-violet-100/70 bg-white/88 pb-[env(safe-area-inset-bottom,0px)] pt-1 shadow-[0_-6px_16px_rgba(76,29,149,0.09)] backdrop-blur-lg dark:border-slate-700 dark:bg-slate-950/85 dark:shadow-[0_-8px_18px_rgba(0,0,0,0.42)]"
    role="complementary"
    aria-label="Sponsor alanı"
>
    <div class="mx-auto flex max-w-4xl items-center gap-1.5 px-2 pb-1 sm:px-3">
        <div class="flex h-[42px] flex-1 items-center rounded-lg border border-violet-100/80 bg-white/95 px-2 shadow-sm dark:border-slate-700 dark:bg-slate-900/90">
            <div class="sticky-ad-mini-slot w-full overflow-hidden text-center text-[11px] leading-tight text-slate-700 dark:text-slate-200 [&_*]:max-w-full">
                {!! $html !!}
            </div>
        </div>
        <button
            type="button"
            class="inline-flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-violet-200 bg-white text-xs font-medium leading-none text-violet-500 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500"
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
        max-height: 36px !important;
    }

    #site-sticky-ad .sticky-ad-mini-slot iframe,
    #site-sticky-ad .sticky-ad-mini-slot ins.adsbygoogle {
        height: 36px !important;
        min-height: 36px !important;
        max-height: 36px !important;
        width: 100% !important;
        max-width: 100% !important;
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

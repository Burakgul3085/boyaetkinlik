{{-- Sabit alt şerit: yalnızca ads_footer doluysa ve genel site yüzeyinde gösterilir (layouts.app). --}}
<div
    id="site-sticky-ad"
    class="pointer-events-auto fixed inset-x-0 bottom-0 z-30 border-t border-violet-100/80 bg-white/85 pb-[env(safe-area-inset-bottom,0px)] pt-2 shadow-[0_-12px_34px_rgba(76,29,149,0.12)] backdrop-blur-xl dark:border-slate-700 dark:bg-slate-950/85 dark:shadow-[0_-12px_34px_rgba(0,0,0,0.5)]"
    role="complementary"
    aria-label="Sponsor alanı"
>
    <div class="mx-auto flex max-w-7xl items-center gap-2 px-3 pb-2 sm:px-4">
        <div class="flex min-h-[64px] flex-1 items-center rounded-2xl border border-violet-100/80 bg-gradient-to-r from-white via-violet-50/35 to-white px-3 py-2 shadow-sm dark:border-slate-700 dark:bg-slate-900/90">
            <div class="w-full overflow-x-auto overflow-y-hidden text-center text-[13px] leading-snug text-slate-700 [-webkit-overflow-scrolling:touch] [scrollbar-width:thin] dark:text-slate-200 [&_*]:max-w-full">
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

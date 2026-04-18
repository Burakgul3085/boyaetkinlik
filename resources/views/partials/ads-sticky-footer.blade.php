{{-- Sabit alt şerit: yalnızca ads_footer doluysa ve genel site yüzeyinde gösterilir (layouts.app). --}}
<div
    id="site-sticky-ad"
    class="pointer-events-auto fixed inset-x-0 bottom-0 z-30 border-t border-slate-200/90 bg-white/95 shadow-[0_-10px_40px_rgba(15,23,42,0.08)] backdrop-blur-md dark:border-slate-700 dark:bg-slate-950/95 dark:shadow-[0_-10px_40px_rgba(0,0,0,0.45)]"
    role="complementary"
    aria-label="Sponsor alanı"
>
    <div class="mx-auto flex max-w-7xl items-start gap-2 px-3 py-2 sm:px-4">
        <div class="max-h-[100px] min-h-0 flex-1 overflow-y-auto overscroll-contain text-center text-[13px] leading-snug text-slate-700 dark:text-slate-200 [&_*]:max-w-full">
            {!! $html !!}
        </div>
        <button
            type="button"
            class="mt-0.5 inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-slate-200 bg-white text-lg leading-none text-slate-500 transition hover:border-violet-300 hover:bg-violet-50 hover:text-violet-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-slate-500"
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

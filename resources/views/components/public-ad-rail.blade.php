@props([
    'tight' => false,
])

@php
    $adsHeader = \App\Models\Setting::getValue('ads_header');
    $adsLeft = \App\Models\Setting::getValue('ads_left');
    $adsRight = \App\Models\Setting::getValue('ads_right');
    $headerMin = $tight ? 'min-h-[96px]' : 'min-h-[128px]';
    $railMin = $tight ? 'min-h-[440px] xl:min-h-[520px]' : 'min-h-[600px] xl:min-h-[680px]';
@endphp

<div {{ $attributes->merge(['class' => 'public-ad-rail space-y-5']) }}>
    <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
        <div class="overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-400 [&_*]:max-w-full">
            <div class="{{ $headerMin }} w-full overflow-hidden rounded-lg bg-white dark:bg-slate-900">
                {!! $adsHeader ?: '<div class="flex '.$headerMin.' items-center justify-center px-3">Üst reklam alanı (Admin → Reklam Alanları → Üst bant)</div>' !!}
            </div>
        </div>
    </section>

    {{-- lg+: yan sütunlar satır yüksekliğine yayılır; içteki kutu sticky ile kaydırırken üstte sabit kalır (fareyi takip etmez, standart reklam davranışı). --}}
    <div class="grid gap-5 lg:grid-cols-12 lg:items-stretch lg:gap-6">
        <aside class="relative hidden min-h-0 min-w-0 lg:col-span-2 lg:block">
            <div class="sticky top-24 z-10 w-full pt-0.5 sm:top-28">
                <div class="max-h-[calc(100dvh-6.5rem)] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                    <p class="mb-2 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sol sütun</p>
                    <div class="{{ $railMin }} min-h-0 overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-400 [&_*]:max-w-full">
                        {!! $adsLeft ?: '<div class="flex '.$railMin.' items-center justify-center px-2">Sol reklam alanı</div>' !!}
                    </div>
                </div>
            </div>
        </aside>

        <div class="min-h-0 min-w-0 lg:col-span-8">
            {{ $slot }}
        </div>

        <aside class="relative hidden min-h-0 min-w-0 lg:col-span-2 lg:block">
            <div class="sticky top-24 z-10 w-full pt-0.5 sm:top-28">
                <div class="max-h-[calc(100dvh-6.5rem)] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                    <p class="mb-2 text-center text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Sağ sütun</p>
                    <div class="{{ $railMin }} min-h-0 overflow-hidden rounded-xl border border-dashed border-slate-300 bg-slate-50 text-center text-sm text-slate-500 dark:border-slate-600 dark:bg-slate-800/60 dark:text-slate-400 [&_*]:max-w-full">
                        {!! $adsRight ?: '<div class="flex '.$railMin.' items-center justify-center px-2">Sağ reklam alanı</div>' !!}
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

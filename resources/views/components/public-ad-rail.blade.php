@props([
    'tight' => false,
])

@php
    $adsHeader = \App\Models\Setting::getValue('ads_header');
    $adsLeft = \App\Models\Setting::getValue('ads_left');
    $adsRight = \App\Models\Setting::getValue('ads_right');
    $stripAdsScripts = static function (?string $markup): string {
        if (! is_string($markup) || trim($markup) === '') {
            return '';
        }
        $cleaned = preg_replace(
            [
                '/<script[^>]*src=["\']https:\/\/pagead2\.googlesyndication\.com\/pagead\/js\/adsbygoogle\.js[^"\']*["\'][^>]*>\s*<\/script>/i',
                '/<script\b[^>]*>\s*\(adsbygoogle\s*=\s*window\.adsbygoogle\s*\|\|\s*\[\]\)\.push\(\s*(?:\{[\s\S]*?\})?\s*\)\s*;?\s*<\/script>/i',
            ],
            '',
            $markup
        );
        return is_string($cleaned) ? trim($cleaned) : trim($markup);
    };
    $adsHeaderMarkup = $stripAdsScripts($adsHeader);
    $adsLeftMarkup = $stripAdsScripts($adsLeft);
    $adsRightMarkup = $stripAdsScripts($adsRight);
    $hasHeaderAd = $adsHeaderMarkup !== '';
    $hasLeftAd = $adsLeftMarkup !== '';
    $hasRightAd = $adsRightMarkup !== '';
    $hasAnySideAd = $hasLeftAd || $hasRightAd;
    $contentSpanClass = ($hasLeftAd && $hasRightAd) ? 'lg:col-span-8' : 'lg:col-span-10';
    $headerMin = $tight ? 'min-h-[96px]' : 'min-h-[128px]';
    $railMin = $tight ? 'min-h-[440px] xl:min-h-[520px]' : 'min-h-[600px] xl:min-h-[680px]';
@endphp

<div {{ $attributes->merge(['class' => 'public-ad-rail min-w-0 max-w-full space-y-5']) }}>
    @if($hasHeaderAd)
        <section class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
            <div class="overflow-hidden rounded-xl text-center [&_*]:max-w-full">
                <div class="{{ $headerMin }} w-full overflow-hidden rounded-lg bg-white dark:bg-slate-900">
                    {!! $adsHeaderMarkup !!}
                </div>
            </div>
        </section>
    @endif

    @if($hasAnySideAd)
        {{-- lg+: yan sütunlar satır yüksekliğine yayılır; içteki kutu sticky ile kaydırırken üstte sabit kalır. --}}
        <div class="grid gap-5 lg:grid-cols-12 lg:items-stretch lg:gap-6">
            @if($hasLeftAd)
                <aside class="relative hidden min-h-0 min-w-0 lg:col-span-2 lg:block">
                    <div class="sticky top-24 z-10 w-full pt-0.5 sm:top-28">
                        <div class="max-h-[calc(100dvh-6.5rem)] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                            <div class="{{ $railMin }} min-h-0 overflow-hidden rounded-xl text-center [&_*]:max-w-full">
                                {!! $adsLeftMarkup !!}
                            </div>
                        </div>
                    </div>
                </aside>
            @endif

            <div class="min-h-0 min-w-0 {{ $contentSpanClass }}">
                {{ $slot }}
            </div>

            @if($hasRightAd)
                <aside class="relative hidden min-h-0 min-w-0 lg:col-span-2 lg:block">
                    <div class="sticky top-24 z-10 w-full pt-0.5 sm:top-28">
                        <div class="max-h-[calc(100dvh-6.5rem)] overflow-y-auto overflow-x-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900/80">
                            <div class="{{ $railMin }} min-h-0 overflow-hidden rounded-xl text-center [&_*]:max-w-full">
                                {!! $adsRightMarkup !!}
                            </div>
                        </div>
                    </div>
                </aside>
            @endif
        </div>
    @else
        {{ $slot }}
    @endif
</div>

@extends('layouts.app')

@section('title', 'Kullanım Koşulları')

@section('content')
    <article class="space-y-6">
        <section class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/70 to-indigo-50 p-6 shadow-sm md:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Kullanım Koşulları</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 md:text-base">
                Bu koşullar, Boya Etkinlik platformunu kullanan tüm ziyaretçiler ve üyeler için geçerlidir. Siteyi kullanarak bu koşulları kabul etmiş sayılırsınız.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Hizmet Şartları</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Platformda sunulan içerikler bilgilendirme ve eğitim amaçlıdır. Site yönetimi, hizmet kapsamını önceden bildirim yapmaksızın güncelleme, değiştirme veya geçici olarak durdurma hakkını saklı tutar.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Sorumluluk Sınırları</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Kullanıcılar, siteyi mevzuata uygun şekilde kullanmaktan sorumludur. Teknik kesinti, bağlantı hatası veya üçüncü taraf kaynaklı sorunlarda platform, kanunen izin verilen ölçüde sorumluluk kabul eder.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">İçerik Kullanımı ve Telif</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Sitede yayınlanan metinler, görseller, tasarımlar ve diğer tüm içerikler telif hakkı kapsamında korunur. İzinsiz çoğaltma, dağıtma veya ticari amaçla kullanım yasaktır.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Kullanıcı tarafından gönderilen içeriklerde, içeriği yükleyen kişi hukuki sorumluluğu taşır. Hak ihlali şüphesi bulunan içerikler inceleme sonucunda kaldırılabilir.
            </p>
        </section>
    </article>
@endsection

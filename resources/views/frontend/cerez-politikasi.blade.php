@extends('layouts.app')

@section('title', 'Çerez Politikası')

@section('content')
    <article class="space-y-6">
        <section class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/70 to-indigo-50 p-6 shadow-sm md:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Çerez Politikası</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 md:text-base">
                Bu politika, Boya Etkinlik platformunda kullanılan çerez türlerini ve kullanıcıların çerez tercihlerini nasıl yönetebileceğini açıklar.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Çerez Nedir?</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Çerezler, ziyaret ettiğiniz web siteleri tarafından tarayıcınıza kaydedilen küçük metin dosyalarıdır. Bu dosyalar site deneyimini iyileştirmek, oturumları yönetmek ve performansı analiz etmek için kullanılır.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Kullandığımız Çerez Türleri</h2>
            <ul class="mt-3 list-disc space-y-2 pl-5 text-sm leading-relaxed text-slate-700">
                <li><span class="font-semibold">Zorunlu çerezler:</span> Güvenlik, oturum ve temel site fonksiyonları için gereklidir.</li>
                <li><span class="font-semibold">Analitik çerezler:</span> Sayfa performansını ve kullanım alışkanlıklarını ölçmeye yardımcı olur.</li>
                <li><span class="font-semibold">Reklam çerezleri:</span> Google AdSense gibi servislerle ilgi alanına uygun reklam gösterimi için kullanılabilir.</li>
            </ul>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Çerez Tercihleri Nasıl Yönetilir?</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Tarayıcı ayarları üzerinden çerezleri kabul edebilir, engelleyebilir veya silebilirsiniz. Çerezlerin devre dışı bırakılması, bazı site özelliklerinin beklenen şekilde çalışmamasına neden olabilir.
            </p>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Kişisel veri işleme süreçleri hakkında kapsamlı bilgi için
                <a href="{{ route('privacy') }}" class="font-semibold text-violet-700 hover:text-violet-800">Gizlilik Politikası</a>
                sayfasını inceleyebilirsiniz.
            </p>
        </section>
    </article>
@endsection

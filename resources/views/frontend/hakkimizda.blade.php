@extends('layouts.app')

@section('title', 'Hakkımızda')

@section('content')
    <section class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/70 to-indigo-50 p-6 shadow-sm md:p-8">
        <h1 class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Hakkımızda</h1>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-700 md:text-base">
            Boya Etkinlik, çocuklar, aileler ve eğitimciler için güvenli, anlaşılır ve kaliteli boyama içerikleri sunmak amacıyla hazırlanmış bir platformdur.
            Amacımız; yaş ve seviyeye uygun içerikleri düzenli bir yapıda sunarak hem eğlenceli hem öğretici bir dijital deneyim sağlamaktır.
        </p>
    </section>

    <section class="mt-6 grid gap-4 md:grid-cols-2">
        <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Misyonumuz</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                Çocukların yaratıcılığını destekleyen, ailelerin güvenle kullanabileceği ve eğitimcilerin fayda sağlayabileceği içerikleri tek bir çatı altında buluşturmak.
            </p>
        </article>
        <article class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">İçerik Yaklaşımımız</h2>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                İçeriklerimiz yaş gruplarına ve öğrenme seviyelerine göre sınıflandırılır. Her yeni içerikte okunabilir başlıklar, açıklamalar ve kolay gezinme ilkesi korunur.
            </p>
        </article>
    </section>

    <section class="mt-6 rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
        <h2 class="text-xl font-semibold text-slate-900">Aile ve Çocuk Güvenliği</h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-700">
            Platformda çocuk ve aile dostu bir yayın politikası benimsenir. Uygunsuz içeriklere yer verilmez, dış bağlantılar düzenli aralıklarla gözden geçirilir ve kullanıcı deneyimini olumsuz etkileyebilecek uygulamalardan kaçınılır.
        </p>
        <p class="mt-3 text-sm leading-relaxed text-slate-700">
            Veri işleme süreçlerinde gizlilik ilkesi gözetilir. Detaylı bilgilendirme için
            <a href="{{ route('privacy') }}" class="font-semibold text-violet-700 hover:text-violet-800">Gizlilik Politikası</a>
            ve
            <a href="{{ route('cookies') }}" class="font-semibold text-violet-700 hover:text-violet-800">Çerez Politikası</a>
            sayfalarını inceleyebilirsiniz.
        </p>
    </section>
@endsection

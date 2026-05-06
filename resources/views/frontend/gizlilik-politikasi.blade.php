@extends('layouts.app')

@section('title', 'Gizlilik Politikası')

@section('content')
    <article class="space-y-6">
        <section class="rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/70 to-indigo-50 p-6 shadow-sm md:p-8">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Gizlilik Politikası</h1>
            <p class="mt-3 text-sm leading-relaxed text-slate-700 md:text-base">
                Bu politika, Boya Etkinlik platformunda kişisel verilerin nasıl toplandığını, işlendiğini, korunduğunu ve kullanıcı haklarının nasıl kullanılabileceğini açıklar.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Toplanan Veriler</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Hizmetin sunulabilmesi için ad, soyad, e-posta adresi ve kullanıcı etkileşim bilgileri gibi veriler işlenebilir. Toplanan veriler yalnızca hizmet kalitesini artırma, hesap güvenliği ve yasal yükümlülüklerin yerine getirilmesi amaçlarıyla kullanılır.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Çerezler ve Benzeri Teknolojiler</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Sitemizde kullanıcı deneyimini iyileştirmek, oturum yönetimini sağlamak ve performans ölçümü yapmak için çerezler kullanılabilir. Çerezler hakkında detaylı bilgi için
                <a href="{{ route('cookies') }}" class="font-semibold text-violet-700 hover:text-violet-800">Çerez Politikası</a>
                sayfasını inceleyebilirsiniz.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Üçüncü Taraf Servisler</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Platformda Google Analytics ve Google AdSense gibi üçüncü taraf servisler kullanılabilir. Bu servisler kendi gizlilik politikaları kapsamında veri işleyebilir. Kullanıcılar, ilgili servislerin politika metinlerini ayrıca incelemelidir.
            </p>
        </section>

        <section class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            <h2 class="text-xl font-semibold text-slate-900">Kullanıcı Hakları</h2>
            <p class="mt-3 text-sm leading-relaxed text-slate-700">
                Kullanıcılar, yürürlükteki mevzuat kapsamında kişisel verilerine ilişkin bilgi talep etme, düzeltme, silme veya işlemeyi kısıtlama haklarına sahiptir. Taleplerinizi iletişim kanallarımız üzerinden iletebilirsiniz.
            </p>
        </section>
    </article>
@endsection

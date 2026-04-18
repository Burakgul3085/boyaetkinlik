@extends('layouts.admin')

@section('title', 'Reklam Alanları')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Reklam alanları</h1>
    <p class="mt-1 max-w-3xl text-sm text-slate-600">
        Buraya yapıştırdığınız kodlar (Google AdSense, manuel banner vb.) <strong>otomatik olarak ziyaretçi arayüzündeki sabit yerlere</strong> yerleşir.
        Yeni kategori veya boyama sayfası eklendiğinde ekstra ayar gerekmez; aynı bileşen tüm ilgili sayfalarda kullanılır.
    </p>

    <div class="card mt-5 space-y-4 p-5">
        <h2 class="text-lg font-semibold text-slate-900">Nereye ne düşer?</h2>
        <div class="overflow-x-auto rounded-xl border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-4 py-3">Alan adı</th>
                        <th class="px-4 py-3">Sitede konumu</th>
                        <th class="px-4 py-3">Göründüğü sayfalar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">Üst bant</td>
                        <td class="px-4 py-3">İçerik sütununun hemen üstünde, tam genişlikte yatay kutu (masaüstünde sol–orta–sağ ızgaranın üst çizgisiyle hizalı).</td>
                        <td class="px-4 py-3">Anasayfa, kategori, boyama detayı, Shopier yönlendirme, indirme formatı seçimi vb. (İletişim ve yönetim paneli hariç.)</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">Sol sütun</td>
                        <td class="px-4 py-3">Orta içeriğin solunda; yalnızca <strong>geniş ekran (lg+)</strong>. Sayfa aşağı/yukarı kaydırıldığında kutu, üst menünün altında <em>yapışkan (sticky)</em> kalır; fare imlecini takip etmez (reklam ağları için doğru davranış).</td>
                        <td class="px-4 py-3">Üst bantla aynı genel sayfalar.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">Sağ sütun</td>
                        <td class="px-4 py-3">Orta içeriğin sağında; yine lg+ ve sol ile aynı yapışkan kaydırma davranışı.</td>
                        <td class="px-4 py-3">Üst bantla aynı genel sayfalar.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">Boyama detayı içi</td>
                        <td class="px-4 py-3">Boyama sayfasında açıklama metninin altında, indir / satın al kutularından <strong>önce</strong> ayrı bir kart içinde.</td>
                        <td class="px-4 py-3">Yalnızca <code class="rounded bg-slate-100 px-1">/boyama/{id}</code> ürün detayı.</td>
                    </tr>
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">Alt şerit (sabit)</td>
                        <td class="px-4 py-3">Tarayıcı penceresinin altına sabitlenmiş ince şerit; yükseklik sınırlı, yumuşak kenarlık ve blur. Ziyaretçi <strong>×</strong> ile kapatabilir (oturum boyunca hatırlanır).</td>
                        <td class="px-4 py-3">Üst üçlü şeritle aynı genel yüzey; içerik ile çakışmaması için sayfaya alt boşluk eklenir. İletişim ve admin yine yok.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="rounded-xl border border-amber-200 bg-amber-50/80 p-4 text-sm text-amber-950">
            <p class="font-semibold">Dikkat</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>AdSense çoklu reklam politikasına uyum sizin sorumluluğunuzdadır.</li>
                <li>Alt şeritte parlayan animasyonlardan kaçının; ziyaretçiyi yormamak için kısa ve sade kodlar önerilir.</li>
                <li>Mobilde yan sütunlar gizlenir; önemli çağrıları üst bant veya alt şeride tutmanız iyi olur.</li>
            </ul>
        </div>
    </div>

    <form method="post" action="{{ route('admin.ads.update') }}" class="card mt-5 space-y-6 p-5">
        @csrf

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-900" for="ads_header">1) Üst bant — <span class="font-normal text-slate-600">ads_header</span></label>
            <p class="mb-2 text-xs text-slate-500">Yatay reklam birimi (ör. 728×90 veya responsive display). Boş bırakılırsa sitede gri çerçeveli yer tutucu görünür.</p>
            <textarea id="ads_header" name="ads_header" rows="5" class="input-ui font-mono text-xs" placeholder="&lt;script&gt;...&lt;/script&gt;">{{ $settings['ads_header'] ?? '' }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-900" for="ads_left">2) Sol sütun — <span class="font-normal text-slate-600">ads_left</span></label>
            <p class="mb-2 text-xs text-slate-500">Dikey / geniş skyscraper tarzı birimler için. Sadece masaüstünde görünür.</p>
            <textarea id="ads_left" name="ads_left" rows="5" class="input-ui font-mono text-xs" placeholder="Sol sütun reklam kodu">{{ $settings['ads_left'] ?? '' }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-900" for="ads_right">3) Sağ sütun — <span class="font-normal text-slate-600">ads_right</span></label>
            <p class="mb-2 text-xs text-slate-500">Sol ile aynı mantık; içerik ortada kalır, reklamlar kenarda hizalanır.</p>
            <textarea id="ads_right" name="ads_right" rows="5" class="input-ui font-mono text-xs" placeholder="Sağ sütun reklam kodu">{{ $settings['ads_right'] ?? '' }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-900" for="ads_product_detail">4) Boyama detayı içi — <span class="font-normal text-slate-600">ads_product_detail</span></label>
            <p class="mb-2 text-xs text-slate-500">Sadece ürün detay şablonunda, görsel ve açıklamadan hemen sonra tek blok halinde.</p>
            <textarea id="ads_product_detail" name="ads_product_detail" rows="5" class="input-ui font-mono text-xs" placeholder="Ürün detay içi reklam">{{ $settings['ads_product_detail'] ?? '' }}</textarea>
        </div>

        <div>
            <label class="mb-2 block text-sm font-semibold text-slate-900" for="ads_footer">5) Alt sabit şerit — <span class="font-normal text-slate-600">ads_footer</span></label>
            <p class="mb-2 text-xs text-slate-500">İnce yatay banner veya küçük HTML snippet. Boşsa şerit hiç oluşturulmaz. Sabit konumdadır; kapatıldığında oturum süresince gizlenir.</p>
            <textarea id="ads_footer" name="ads_footer" rows="4" class="input-ui font-mono text-xs" placeholder="Alt şerit (isteğe bağlı)">{{ $settings['ads_footer'] ?? '' }}</textarea>
        </div>

        <div class="sticky bottom-3 rounded-xl border border-indigo-100 bg-white/90 p-3 shadow-sm backdrop-blur">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-slate-500">Kaydettiğinizde tüm uygun sayfalar bir sonraki yüklemede güncellenir.</p>
                <button type="submit" class="btn-primary px-5">Değişiklikleri güncelle</button>
            </div>
        </div>
    </form>
@endsection

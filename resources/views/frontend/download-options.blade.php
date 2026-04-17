@extends('layouts.app')

@section('title', 'İndirme Formatı Seç')

@section('content')
    <section class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">İndirme Adımı</p>
                <h1 class="mt-2 text-2xl font-semibold text-slate-900">Dosya formatını seçin</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Satın aldığınız içerik için uygun formatı seçebilirsiniz. Seçim yaptıktan sonra indirme linki tek kullanımlık olarak tamamlanır.
                </p>

                <div class="mt-6 grid gap-3 sm:grid-cols-2">
                    @php
                        $originalFormat = strtolower(pathinfo($transaction->coloringPage->pdf_path, PATHINFO_EXTENSION) ?: 'pdf');
                        $singleOriginalPdf = count($downloadFormats) === 1 && strtolower($downloadFormats[0]) === 'pdf' && $originalFormat === 'pdf';
                    @endphp
                    @if($singleOriginalPdf)
                        <div class="sm:col-span-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm text-slate-600">Dosya zaten PDF formatında. Doğrudan indirebilirsiniz.</p>
                            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                <a
                                    href="{{ route('download.paid', ['token' => $transaction->download_token, 'format' => 'pdf']) }}"
                                    class="btn-primary inline-flex w-full items-center justify-center"
                                >
                                    PDF İndir
                                </a>
                                <a
                                    href="{{ route('download.paid.print', ['token' => $transaction->download_token, 'format' => 'pdf']) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn-secondary inline-flex w-full items-center justify-center"
                                >
                                    Dosyayı Yazdır
                                </a>
                            </div>
                        </div>
                    @else
                        @foreach ($downloadFormats as $format)
                            @php
                                $formatLabel = strtolower($format) === $originalFormat
                                    ? 'Orijinal ('.strtoupper($format).')'
                                    : (strtolower($format) === 'pdf' ? "PDF'e Dönüştür" : strtoupper($format).' Dönüştür');
                            @endphp
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                                <p class="text-sm font-semibold text-slate-700">{{ $formatLabel }}</p>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    <a
                                        href="{{ route('download.paid', ['token' => $transaction->download_token, 'format' => $format]) }}"
                                        class="btn-primary inline-flex w-full items-center justify-center"
                                    >
                                        İndir
                                    </a>
                                    <a
                                        href="{{ route('download.paid.print', ['token' => $transaction->download_token, 'format' => $format]) }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn-secondary inline-flex w-full items-center justify-center"
                                    >
                                        Yazdır
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

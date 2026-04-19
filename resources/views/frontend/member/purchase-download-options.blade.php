@extends('layouts.app')

@section('title', 'İndirme Seçenekleri')

@section('content')
<section class="mx-auto max-w-3xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">İndirme Seçenekleri</h1>
        <p class="mt-1 text-sm text-slate-500">{{ $transaction->coloringPage->title }} için format seçin.</p>

        <div class="mt-5 grid gap-2 sm:grid-cols-2">
            @foreach($downloadFormats as $format)
                <a
                    href="{{ ($sharedDownload ?? false)
                        ? \Illuminate\Support\Facades\URL::temporarySignedRoute('member.purchases.shared-download', now()->addDays(30), ['transaction' => $transaction, 'format' => $format])
                        : route('member.purchases.download', ['transaction' => $transaction, 'format' => $format]) }}"
                    class="inline-flex items-center justify-center rounded-xl border border-violet-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-violet-300 hover:text-violet-700"
                >
                    {{ strtoupper($format) }} İndir
                </a>
            @endforeach
        </div>
    </div>
</section>
@endsection

@extends('layouts.app')

@section('title', 'Misafir indirme linki')

@section('content')
    <x-public-ad-rail :tight="true">
        <section class="py-12">
            <div class="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Misafir satın alım</p>
                    <h1 class="mt-2 text-2xl font-semibold text-slate-900 dark:text-slate-100">İndirme linkini tekrar gönder</h1>
                    <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">
                        Üye olmadan yaptığınız ödemelerde indirme adresi ödeme sırasında kullandığınız e-postaya gönderilir.
                        Sayfayı kapattıysanız veya bağlantıyı kaybettiyseniz, aynı e-posta adresiyle buradan yeniden talep edebilirsiniz.
                    </p>

                    @if(session('recovery_status'))
                        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
                            {{ session('recovery_status') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-800 dark:bg-rose-950/40 dark:text-rose-200">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="post" action="{{ route('guest.purchase.recovery.submit') }}" class="mt-6 space-y-4">
                        @csrf
                        <div>
                            <label for="recovery_email" class="block text-sm font-medium text-slate-700 dark:text-slate-300">Ödeme sırasında kullandığınız e-posta</label>
                            <input
                                type="email"
                                name="email"
                                id="recovery_email"
                                required
                                value="{{ old('email') }}"
                                autocomplete="email"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            >
                        </div>
                        <div>
                            <label for="recovery_order" class="block text-sm font-medium text-slate-700 dark:text-slate-300">
                                Sipariş numarası <span class="font-normal text-slate-500">(isteğe bağlı)</span>
                            </label>
                            <input
                                type="text"
                                name="order_id"
                                id="recovery_order"
                                value="{{ old('order_id') }}"
                                placeholder="Örn. ORD-XXXXXXXXXX"
                                class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-slate-900 shadow-sm transition focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100"
                            >
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Birden fazla satın alımınız varsa tek bir ürünü daraltmak için yazabilirsiniz.</p>
                        </div>
                        <button type="submit" class="btn-primary inline-flex w-full items-center justify-center py-3">
                            Linkleri e-postama gönder
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </x-public-ad-rail>
@endsection

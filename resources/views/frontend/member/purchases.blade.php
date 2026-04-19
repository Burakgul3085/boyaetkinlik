@extends('layouts.app')

@section('title', 'Satın Alınanlar')

@section('content')
<section class="mx-auto max-w-5xl">
    <div class="card p-6 md:p-7">
        <h1 class="text-2xl font-bold text-slate-900">Satın Alınanlar</h1>
        <p class="mt-1 text-sm text-slate-500">Ödemesi tamamlanan ürünleri tekrar tekrar indirebilirsiniz.</p>

        @if(session('support_success'))
            <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                <p class="font-semibold">Mesajınız sayfa yöneticisine iletilmiştir.</p>
                <p class="mt-1 text-emerald-800/90">Ortalama çözüm süresi yaklaşık <strong>3 saat</strong> olarak planlanmaktadır; yoğunluğa göre değişebilir. Yanıt hazır olduğunda bu sayfada görebilir ve e-posta ile bilgilendirileceksiniz.</p>
            </div>
        @endif

        @if($errors->has('member_message') || $errors->has('transaction_id'))
            <div class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                {{ $errors->first('member_message') ?: $errors->first('transaction_id') }}
            </div>
        @endif

        <div class="mt-6 space-y-3">
            @forelse($purchases as $purchase)
                @php
                    $txTickets = $supportByTransaction->get((string) $purchase->id, collect());
                @endphp
                <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $purchase->coloringPage->title }}</p>
                            <p class="text-sm text-slate-500">Sipariş: {{ $purchase->order_id }} - {{ number_format((float) $purchase->paid_amount, 2) }} TL</p>
                        </div>
                        <a href="{{ route('member.purchases.download', $purchase) }}" class="btn-primary">İndir</a>
                    </div>
                    <form method="post" action="{{ route('member.purchases.email', $purchase) }}" class="mt-4 grid gap-2 sm:grid-cols-[1fr_auto]">
                        @csrf
                        <input
                            type="email"
                            name="email"
                            required
                            maxlength="255"
                            placeholder="Dosya linki gönderilecek e-posta"
                            class="input-ui"
                            value="{{ auth()->user()->email }}"
                        >
                        <button class="btn-secondary">E-postaya Gönder</button>
                    </form>

                    @if($txTickets->isNotEmpty())
                        <div class="mt-5 space-y-4 border-t border-violet-100 pt-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bu ürün için destek mesajlarınız</p>
                            @foreach($txTickets as $ticket)
                                <div class="rounded-xl border border-violet-100 bg-violet-50/40 p-4 text-sm">
                                    <p class="text-xs text-slate-500">{{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                                    <p class="mt-2 whitespace-pre-wrap text-slate-800">{{ $ticket->member_message }}</p>
                                    @if($ticket->isAnswered())
                                        <div class="mt-3 rounded-lg border border-emerald-200 bg-white p-3">
                                            <p class="text-xs font-semibold text-emerald-800">Yönetici yanıtı {{ $ticket->admin_replied_at?->format('d.m.Y H:i') }}</p>
                                            <p class="mt-1 whitespace-pre-wrap text-slate-800">{{ $ticket->admin_reply }}</p>
                                        </div>
                                    @else
                                        <p class="mt-2 text-xs text-amber-700">Yanıt bekleniyor.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <form method="post" action="{{ route('member.purchases.support.store') }}" class="mt-4 space-y-2 border-t border-dashed border-slate-200 pt-4">
                        @csrf
                        <input type="hidden" name="transaction_id" value="{{ $purchase->id }}">
                        <label class="block text-sm font-medium text-slate-700">Bu satın alımla ilgili sorun / soru</label>
                        <textarea name="member_message" rows="3" required minlength="10" maxlength="4000" class="input-ui w-full" placeholder="Kısaca sorununuzu yazın (en az 10 karakter).">{{ (string) old('transaction_id') === (string) $purchase->id ? old('member_message') : '' }}</textarea>
                        <button type="submit" class="btn-secondary text-sm">Mesajı gönder</button>
                    </form>
                </article>
            @empty
                <p class="rounded-xl border border-violet-100 bg-violet-50/40 p-4 text-sm text-slate-600">Henüz tamamlanmış bir satın alma görünmüyor.</p>
            @endforelse
        </div>

        @php
            $generalTickets = $supportByTransaction->get('_general', collect());
        @endphp

        @if($generalTickets->isNotEmpty())
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Genel destek mesajlarınız</p>
                <div class="mt-4 space-y-4">
                    @foreach($generalTickets as $ticket)
                        <div class="rounded-xl border border-violet-100 bg-violet-50/40 p-4 text-sm">
                            <p class="text-xs text-slate-500">{{ $ticket->created_at?->format('d.m.Y H:i') }}</p>
                            <p class="mt-2 whitespace-pre-wrap text-slate-800">{{ $ticket->member_message }}</p>
                            @if($ticket->isAnswered())
                                <div class="mt-3 rounded-lg border border-emerald-200 bg-white p-3">
                                    <p class="text-xs font-semibold text-emerald-800">Yönetici yanıtı {{ $ticket->admin_replied_at?->format('d.m.Y H:i') }}</p>
                                    <p class="mt-1 whitespace-pre-wrap text-slate-800">{{ $ticket->admin_reply }}</p>
                                </div>
                            @else
                                <p class="mt-2 text-xs text-amber-700">Yanıt bekleniyor.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-8 rounded-2xl border border-indigo-100 bg-indigo-50/30 p-5">
            <h2 class="text-sm font-semibold text-slate-900">Hesap veya ödeme ile ilgili genel sorun</h2>
            <p class="mt-1 text-xs text-slate-600">Belirli bir sipariş seçmeden mesaj göndermek için aşağıyı kullanın.</p>
            <form method="post" action="{{ route('member.purchases.support.store') }}" class="mt-4 space-y-2">
                @csrf
                <textarea name="member_message" rows="4" required minlength="10" maxlength="4000" class="input-ui w-full" placeholder="Sorununuzu yazın (en az 10 karakter).">{{ old('transaction_id') ? '' : old('member_message') }}</textarea>
                <button type="submit" class="btn-primary text-sm">Genel mesaj gönder</button>
            </form>
        </div>
    </div>
</section>
@endsection

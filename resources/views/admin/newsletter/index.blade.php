@extends('layouts.admin')

@section('title', 'E-Bülten Yönetimi')

@section('content')
    <section class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">E-Bülten</p>
            <h1 class="mt-2 text-2xl font-semibold text-slate-900">Abone Yönetimi ve Gönderim</h1>
            <p class="mt-2 text-sm text-slate-600">
                Footer alanından kayıt olan kullanıcıları burada görür, tek tek veya toplu olarak e-posta gönderebilirsiniz.
            </p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Toplam Abone</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $totalSubscribers }}</p>
                </div>
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Daha Önce Mesaj Gönderilen</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900">{{ $contactedSubscribers }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Toplu E-Bülten Gönder</h2>
            <p class="mt-1 text-sm text-slate-600">Bu alandan tüm kayıtlı abonelere aynı mesajı tek seferde gönderebilirsiniz.</p>
            <form method="post" action="{{ route('admin.newsletter.send.bulk') }}" class="mt-4 grid gap-3">
                @csrf
                <label class="input-ui">
                    Konu
                    <input type="text" name="subject" required class="mt-1 w-full" placeholder="Örn: Nisan ayı yeni içerikler">
                </label>
                <label class="input-ui">
                    Mesaj
                    <textarea name="message" rows="5" required class="mt-1 w-full" placeholder="Abonelere göndermek istediğiniz mesajı yazın."></textarea>
                </label>
                <button class="btn-primary w-full sm:w-auto">Tüm Abonelere Gönder</button>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-lg font-semibold text-slate-900">Aboneler</h2>
            <p class="mt-1 text-sm text-slate-600">Her satırdan kişiye özel e-posta gönderebilirsiniz.</p>

            <div class="mt-4 space-y-4">
                @forelse($subscribers as $subscriber)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $subscriber->full_name }}</p>
                                <p class="text-sm text-slate-600">{{ $subscriber->email }}</p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Son gönderim:
                                    {{ $subscriber->last_contacted_at ? $subscriber->last_contacted_at->format('d.m.Y H:i') : 'Henüz yok' }}
                                </p>
                            </div>
                            <form method="post" action="{{ route('admin.newsletter.destroy', $subscriber) }}" onsubmit="return confirm('Bu aboneyi silmek istediğinize emin misiniz?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn-danger w-full sm:w-auto">Sil</button>
                            </form>
                        </div>

                        <form method="post" action="{{ route('admin.newsletter.send') }}" class="mt-4 grid gap-2">
                            @csrf
                            <input type="hidden" name="subscriber_id" value="{{ $subscriber->id }}">
                            <label class="input-ui">
                                Konu
                                <input type="text" name="subject" required class="mt-1 w-full" placeholder="Örn: Size özel bilgilendirme">
                            </label>
                            <label class="input-ui">
                                Mesaj
                                <textarea name="message" rows="4" required class="mt-1 w-full" placeholder="{{ $subscriber->first_name }} için mesajınızı yazın"></textarea>
                            </label>
                            <button class="btn-secondary w-full sm:w-auto">Bu Aboneye Gönder</button>
                        </form>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm text-slate-500">
                        Henüz e-bülten kaydı bulunmuyor.
                    </div>
                @endforelse
            </div>

            <div class="mt-4">
                {{ $subscribers->links() }}
            </div>
        </div>
    </section>
@endsection

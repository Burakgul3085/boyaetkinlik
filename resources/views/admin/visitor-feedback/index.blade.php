@extends('layouts.admin')

@section('title', 'Ziyaretçi geri bildirimleri')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Ziyaretçi geri bildirimleri</h1>
    <p class="mt-1 max-w-2xl text-sm text-slate-600">
        Ana sayfadaki formdan gelen istek, öneri, şikayet ve yorumlar burada listelenir. Onayladığınız kayıtlar sitede görünür; e-posta sütununu açıp kapatabilirsiniz.
    </p>

    <form method="post" action="{{ route('admin.visitor-feedback.settings') }}" class="card mt-5 flex flex-wrap items-center justify-between gap-4 p-4">
        @csrf
        <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-700">
            <input type="checkbox" name="visitor_feedback_reply_email_enabled" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" @checked($replyEmailEnabled)>
            <span>Yanıtı yayınlarken ziyaretçiye bilgilendirme e-postası gönder</span>
        </label>
        <button type="submit" class="btn-secondary text-sm">E-posta ayarını kaydet</button>
    </form>

    <div class="mt-4 flex flex-wrap gap-2 text-sm">
        <a href="{{ route('admin.visitor-feedback.index') }}" class="rounded-lg px-3 py-1.5 font-medium {{ $statusFilter === '' ? 'bg-indigo-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">Tümü</a>
        <a href="{{ route('admin.visitor-feedback.index', ['durum' => 'bekleyen']) }}" class="rounded-lg px-3 py-1.5 font-medium {{ $statusFilter === 'bekleyen' ? 'bg-amber-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">Bekleyen</a>
        <a href="{{ route('admin.visitor-feedback.index', ['durum' => 'yayinda']) }}" class="rounded-lg px-3 py-1.5 font-medium {{ $statusFilter === 'yayinda' ? 'bg-emerald-600 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300' }}">Yayındaki</a>
    </div>

    <div class="mt-6 space-y-5">
        @forelse($items as $item)
            <article class="card overflow-hidden p-5">
                <div class="flex flex-wrap items-start justify-between gap-3 border-b border-slate-100 pb-3 dark:border-slate-700">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $item->first_name }} {{ $item->last_name }}</p>
                        <p class="text-xs text-slate-500">{{ $item->created_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($item->is_approved)
                            <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800">Onaylı</span>
                        @else
                            <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-900">Bekliyor</span>
                        @endif
                        <span class="text-amber-600" title="Yıldız">{{ str_repeat('★', min(5, max(1, (int) $item->rating))) }}{{ str_repeat('☆', 5 - min(5, max(1, (int) $item->rating))) }}</span>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">E-posta (yönetim): <a class="font-medium text-indigo-600 hover:underline" href="mailto:{{ $item->email }}">{{ $item->email }}</a></p>
                <p class="mt-2 whitespace-pre-wrap text-sm text-slate-700">{{ $item->body }}</p>

                <div class="mt-4 flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-700">
                    @if(! $item->is_approved)
                        <form method="post" action="{{ route('admin.visitor-feedback.approve', $item) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-primary text-sm">Onayla (sitede göster)</button>
                        </form>
                    @endif
                    @if($item->is_approved)
                        <form method="post" action="{{ route('admin.visitor-feedback.toggle-email', $item) }}" class="inline">
                            @csrf
                            <button type="submit" class="btn-secondary text-sm">
                                Sitede e-posta: {{ $item->show_email_public ? 'Açık → kapat' : 'Kapalı → göster' }}
                            </button>
                        </form>
                    @endif
                    <form method="post" action="{{ route('admin.visitor-feedback.destroy', $item) }}" class="inline" onsubmit="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-rose-200 bg-rose-50 px-3 py-1.5 text-sm font-semibold text-rose-800 transition hover:bg-rose-100">Sil</button>
                    </form>
                </div>

                <div class="mt-5 rounded-xl border border-indigo-100 bg-indigo-50/40 p-4 dark:border-indigo-900 dark:bg-indigo-950/40">
                    <p class="text-xs font-semibold uppercase tracking-wide text-indigo-800 dark:text-indigo-200">Yönetici yanıtı</p>
                    @if($item->admin_reply_published)
                        <p class="mt-1 text-xs text-emerald-700 dark:text-emerald-300">Yanıt yayımda @if($item->reply_email_sent_at) · e-posta gönderildi @endif</p>
                    @endif
                    <form method="post" action="{{ route('admin.visitor-feedback.reply', $item) }}" class="mt-2 space-y-2">
                        @csrf
                        <textarea name="admin_reply" rows="4" class="input-ui text-sm" placeholder="Yanıt metni…" required>{{ $item->admin_reply }}</textarea>
                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="btn-secondary text-sm">Yanıtı kaydet</button>
                        </div>
                    </form>
                    @if($item->is_approved && trim((string) $item->admin_reply) !== '')
                        @if(! $item->admin_reply_published)
                            <form method="post" action="{{ route('admin.visitor-feedback.publish-reply', $item) }}" class="mt-2 inline">
                                @csrf
                                <button type="submit" class="btn-primary text-sm">Yanıtı yayınla</button>
                            </form>
                        @endif
                    @endif
                </div>
            </article>
        @empty
            <p class="card p-6 text-sm text-slate-500">Bu filtrede kayıt yok.</p>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $items->links() }}
    </div>
@endsection

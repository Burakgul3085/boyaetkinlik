@extends('layouts.app')

@section('title', 'Boyama Odası')

@push('scripts')
    @vite('resources/js/paint-room.js')
@endpush

@section('content')
<section
    class="mx-auto max-w-4xl"
    id="paint-room-lobby"
    data-status-url="{{ route('paint-room.status', $room) }}"
    data-signal-poll-url="{{ route('paint-room.signals.poll.post', $room) }}"
    data-signal-send-url="{{ route('paint-room.signals.send', $room) }}"
    data-leave-url="{{ route('paint-room.leave', $room) }}"
    data-index-url="{{ route('paint-room.index') }}"
    data-role="{{ $role }}"
    data-expires-at="{{ $expiresAtIso }}"
    data-csrf="{{ csrf_token() }}"
    data-guest-token="{{ $guestAccessToken }}"
    data-ice-servers='@json($iceServers)'
    data-health-url="{{ route('paint-room.signals.health', $room) }}"
>
    <div class="card overflow-hidden p-0">
        <div class="border-b border-violet-100 bg-gradient-to-r from-violet-50 to-teal-50 px-6 py-5 md:px-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-violet-600">Görüntülü boyama odası</p>
                    <h1 class="mt-1 text-2xl font-bold text-slate-900">
                        @if($role === 'owner')
                            Odanız hazır
                        @else
                            Odaya katıldınız
                        @endif
                    </h1>
                </div>
                <div class="paint-room-pill" id="paint-room-timer" aria-live="polite">30:00</div>
            </div>
        </div>

        <div class="space-y-5 p-6 md:p-8">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif

            <div class="flex flex-wrap items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4">
                <div class="paint-room-occupancy" id="paint-room-occupancy">
                    <span class="paint-room-occupancy__count" id="paint-room-count">{{ $room->participantCount() }}</span>
                    <span class="paint-room-occupancy__label">/ 2 kişi</span>
                </div>
                <p class="text-sm text-slate-600" id="paint-room-status-text">
                    @if($room->hasGuest())
                        Görüntülü bağlantı hazırlanıyor…
                    @else
                        Misafir bekleniyor…
                    @endif
                </p>
            </div>

            {{-- Görüntülü sohbet (WebRTC P2P) --}}
            <div class="paint-room-video-panel rounded-2xl border border-violet-100 bg-slate-900/95 p-4">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-bold uppercase tracking-wide text-violet-200">Görüntülü oda</p>
                    <p class="text-xs text-slate-400" id="paint-room-webrtc-status">Kamera ve mikrofon izni bekleniyor…</p>
                </div>
                <p class="mb-2 hidden rounded-lg bg-slate-800/80 px-3 py-2 font-mono text-[10px] leading-relaxed text-slate-300" id="paint-room-debug"></p>
                <div class="paint-room-video-grid">
                    <div class="paint-room-video-tile">
                        <video id="paint-room-local" class="paint-room-video" autoplay playsinline muted></video>
                        <span class="paint-room-video-label">Siz</span>
                    </div>
                    <div class="paint-room-video-tile">
                        <video id="paint-room-remote" class="paint-room-video" autoplay playsinline></video>
                        <span class="paint-room-video-label" id="paint-room-remote-label">Karşı taraf</span>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" id="paint-room-toggle-mic" class="btn-secondary hidden text-xs">Mikrofonu kapat</button>
                    <button type="button" id="paint-room-toggle-cam" class="btn-secondary hidden text-xs">Kamerayı kapat</button>
                </div>
            </div>

            @if($role === 'owner')
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl border border-violet-100 bg-violet-50/50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">PIN</p>
                        <p class="mt-2 font-mono text-3xl font-bold tracking-[0.35em] text-violet-800" id="paint-room-pin">{{ $pin }}</p>
                        <button type="button" class="btn-secondary mt-3 text-xs" data-copy-target="paint-room-pin">PIN kopyala</button>
                    </div>
                    <div class="rounded-2xl border border-teal-100 bg-teal-50/50 p-4">
                        <p class="text-xs font-bold uppercase text-slate-500">Tek kullanımlık davet linki</p>
                        <input type="text" readonly value="{{ $inviteUrl }}" id="paint-room-invite-url" class="input-ui mt-2 text-xs">
                        <button type="button" class="btn-secondary mt-3 text-xs" data-copy-target="paint-room-invite-url">Link kopyala</button>
                        @if($room->inviteLinkUsed())
                            <p class="mt-2 text-xs text-amber-700">Link kullanıldı — misafir PIN ile de girebilir.</p>
                        @endif
                    </div>
                </div>
                <form method="post" action="{{ route('paint-room.leave', $room) }}" id="paint-room-close-form">
                    @csrf
                    <button type="submit" class="btn-danger w-full sm:w-auto">Odayı kapat ve ayrıl</button>
                </form>
            @else
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    Oda sahibi ayrılırsa oda kapanır. Beraber boyama özelliği yakında eklenecek.
                </div>
                <form method="post" action="{{ route('paint-room.leave', $room) }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Odadan ayrıl</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection

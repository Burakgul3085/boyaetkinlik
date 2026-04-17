@extends('layouts.app')

@section('title', 'İletişim')

@section('content')
    <section class="mx-auto max-w-6xl">
        <div class="card-soft p-6 md:p-8">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">İletişim Merkezi</h1>
                    <p class="mt-2 text-sm text-slate-600 md:text-base">
                        Sorularınız, önerileriniz veya iş birlikleri için bize e-posta ya da WhatsApp üzerinden kolayca ulaşabilirsiniz.
                    </p>
                </div>
                <div class="rounded-xl border border-violet-100 bg-white px-4 py-3 text-xs text-slate-600">
                    <p class="font-semibold text-slate-800">Hızlı Bilgi</p>
                    <p class="mt-1">Ortalama dönüş süresi: <span class="font-medium text-violet-700">24 saat</span></p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-6 grid gap-6 lg:grid-cols-2">
            <div class="card p-6 md:p-8">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16v16H4z"/><path d="m22 6-10 7L2 6"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">E-posta Formu</h2>
                        <p class="text-xs text-slate-500">Mesajınız doğrudan e-posta kutunuza düşer.</p>
                    </div>
                </div>

                <form method="post" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ad Soyad</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" class="input-ui" required maxlength="120" placeholder="Örn: Ahmet Yılmaz">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">E-posta</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="input-ui" required maxlength="255" placeholder="ornek@mail.com">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Mesaj</label>
                        <textarea name="message" rows="7" class="input-ui" required minlength="10" maxlength="4000" placeholder="Mesajınızı buraya yazın...">{{ old('message') }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Minimum 10 karakter olmalıdır.</p>
                    </div>

                    <button class="btn-primary w-full md:w-auto">Mesaj Gönder</button>
                </form>
            </div>

            <div class="card p-6 md:p-8">
                <div class="mb-5 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.6 6.3A9.2 9.2 0 0 0 3 17.4L2 22l4.8-1.2a9.2 9.2 0 0 0 4.2 1 9.2 9.2 0 0 0 6.5-15.5Zm-6.6 14a7.7 7.7 0 0 1-3.9-1l-.3-.2-2.8.7.8-2.7-.2-.3a7.7 7.7 0 1 1 6.4 3.5Zm4.2-5.8c-.2-.1-1.2-.6-1.4-.7-.2-.1-.3-.1-.4.1l-.6.7c-.1.1-.2.1-.4 0a6.2 6.2 0 0 1-1.8-1.1 6.8 6.8 0 0 1-1.2-1.5c-.1-.2 0-.3.1-.4l.3-.3.2-.4c.1-.1 0-.3 0-.4l-.7-1.6c-.2-.5-.4-.4-.6-.4H8c-.2 0-.4.1-.6.3a2.5 2.5 0 0 0-.8 1.9c0 1.1.8 2.2.9 2.3.1.1 1.7 2.7 4.2 3.7.6.3 1.1.5 1.5.6.6.2 1.2.2 1.6.1.5-.1 1.2-.5 1.4-1 .2-.5.2-1 .1-1.1 0-.1-.2-.2-.4-.3Z"/>
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">WhatsApp Formu</h2>
                        <p class="text-xs text-slate-500">Mesajınız WhatsApp için otomatik hazırlanır.</p>
                    </div>
                </div>

                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    Hedef numara: <strong>+90 539 518 93 39</strong>
                </div>

                <form method="post" action="{{ route('contact.whatsapp') }}" target="_blank" class="space-y-4">
                    @csrf
                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Ad Soyad</label>
                        <input type="text" name="wa_full_name" value="{{ old('wa_full_name') }}" class="input-ui" required maxlength="120" placeholder="Örn: Ahmet Yılmaz">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">E-posta</label>
                        <input type="email" name="wa_email" value="{{ old('wa_email') }}" class="input-ui" required maxlength="255" placeholder="ornek@mail.com">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Mesaj</label>
                        <textarea name="wa_message" rows="7" class="input-ui" required minlength="10" maxlength="4000" placeholder="Mesajınızı buraya yazın...">{{ old('wa_message') }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Form gönderildiğinde WhatsApp konuşma ekranı açılır.</p>
                    </div>

                    <button class="btn-secondary w-full md:w-auto">WhatsApp Mesajı Hazırla</button>
                </form>
            </div>
        </div>
    </section>
@endsection

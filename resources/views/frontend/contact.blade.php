@extends('layouts.app')

@section('title', 'İletişim')

@section('content')
    <section class="mx-auto max-w-3xl">
        <div class="card-soft p-6 md:p-8">
            <h1 class="text-3xl font-bold text-slate-900">İletişim</h1>
            <p class="mt-2 text-sm text-slate-600 md:text-base">
                Sorularınız, önerileriniz veya iş birlikleri için bize mesaj gönderebilirsiniz.
            </p>

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

            <form method="post" action="{{ route('contact.send') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Ad Soyad</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" class="input-ui" required maxlength="120">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">E-posta</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="input-ui" required maxlength="255">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-slate-700">Mesaj</label>
                    <textarea name="message" rows="7" class="input-ui" required minlength="10" maxlength="4000">{{ old('message') }}</textarea>
                </div>

                <button class="btn-primary w-full md:w-auto">Mesaj Gönder</button>
            </form>
        </div>
    </section>
@endsection

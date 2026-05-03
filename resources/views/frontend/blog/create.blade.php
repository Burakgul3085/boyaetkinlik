@extends('layouts.app')

@section('title', 'Blog Yazısı Gönder')

@section('content')
    <section class="overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/80 to-indigo-50 p-6 shadow-sm md:p-8">
        <p class="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm">Topluluğa Katılın</p>
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Blog Yazısı Gönder</h1>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
            Yazınızı başlık, kısa açıklama, detay metin ve görselle birlikte gönderin. Blog yazısı admin onayı sonrası yayınlanır.
        </p>
    </section>

    <section class="mt-6">
        <form method="post" action="{{ route('blog.store') }}" enctype="multipart/form-data" class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <label class="block text-sm font-medium text-slate-700">
                    İsim
                    <input name="author_first_name" value="{{ old('author_first_name') }}" required class="input-ui mt-2">
                </label>
                <label class="block text-sm font-medium text-slate-700">
                    Soyisim
                    <input name="author_last_name" value="{{ old('author_last_name') }}" required class="input-ui mt-2">
                </label>
                <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                    Başlık
                    <input name="title" value="{{ old('title') }}" required class="input-ui mt-2">
                </label>
                <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                    Kısa Açıklama
                    <textarea name="excerpt" required class="input-ui mt-2" rows="3">{{ old('excerpt') }}</textarea>
                </label>
                <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                    Detay Açıklama
                    <textarea name="content" required class="input-ui mt-2" rows="8">{{ old('content') }}</textarea>
                </label>
                <label class="block text-sm font-medium text-slate-700 md:col-span-2">
                    Fotoğraf (opsiyonel)
                    <input type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="input-ui mt-2">
                </label>
            </div>
            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button class="btn-primary">Blogu Gönder</button>
                <a href="{{ route('blog.index') }}" class="btn-secondary">Blog Sayfasına Dön</a>
            </div>
        </form>
    </section>
@endsection

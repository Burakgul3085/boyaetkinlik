@extends('layouts.admin')

@section('title', 'Blog Yönetimi')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Blog Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">Kullanıcıların gönderdiği blog yazılarını onaylayabilir, reddedebilir veya silebilirsiniz.</p>

    <section class="mt-6 space-y-4">
        <div class="card p-5">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h2 class="text-lg font-bold text-slate-900">Onay Bekleyenler ({{ $pendingBlogs->count() }})</h2>
            </div>
            <div class="space-y-3">
                @forelse($pendingBlogs as $blog)
                    <article class="rounded-2xl border border-violet-100 bg-violet-50/40 p-4">
                        <div class="grid gap-4 md:grid-cols-[8rem,1fr]">
                            <div class="overflow-hidden rounded-xl border border-violet-100 bg-transparent p-2">
                                @if($blog->image_path)
                                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="{{ $blog->title }}" class="h-24 w-full object-contain">
                                @else
                                    <div class="flex h-24 items-center justify-center text-xs text-slate-400">Görsel yok</div>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y H:i') }}</p>
                                <h3 class="mt-1 text-base font-bold text-slate-900">{{ $blog->title }}</h3>
                                <p class="mt-2 text-sm text-slate-700">{{ $blog->excerpt }}</p>
                                <details class="mt-2">
                                    <summary class="cursor-pointer text-xs font-semibold text-violet-700">Detayı Gör</summary>
                                    <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $blog->content }}</p>
                                </details>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <form method="post" action="{{ route('admin.blogs.approve', $blog) }}">
                                        @csrf
                                        <button class="btn-primary px-3 py-1.5 text-xs">Onayla</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.blogs.reject', $blog) }}">
                                        @csrf
                                        <button class="btn-secondary px-3 py-1.5 text-xs">Reddet</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.blogs.destroy', $blog) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Blog silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Bekleyen blog yazısı yok.</div>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Onaylananlar ({{ $approvedBlogs->count() }})</h2>
            <div class="mt-3 space-y-2">
                @forelse($approvedBlogs as $blog)
                    <div class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-3">
                        <p class="text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->approved_at?->format('d.m.Y H:i') ?? '-' }}</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $blog->title }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ route('blog.show', $blog) }}" target="_blank" class="btn-secondary px-3 py-1.5 text-xs">Yayını Aç</a>
                            <form method="post" action="{{ route('admin.blogs.reject', $blog) }}">
                                @csrf
                                <button class="btn-secondary px-3 py-1.5 text-xs">Reddete Çek</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Henüz onaylanmış yazı yok.</div>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Reddedilenler ({{ $rejectedBlogs->count() }})</h2>
            <div class="mt-3 space-y-2">
                @forelse($rejectedBlogs as $blog)
                    <div class="rounded-xl border border-rose-100 bg-rose-50/40 p-3">
                        <p class="text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y H:i') }}</p>
                        <p class="text-sm font-semibold text-slate-900">{{ $blog->title }}</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <form method="post" action="{{ route('admin.blogs.approve', $blog) }}">
                                @csrf
                                <button class="btn-primary px-3 py-1.5 text-xs">Onaya Al</button>
                            </form>
                            <form method="post" action="{{ route('admin.blogs.destroy', $blog) }}">
                                @csrf
                                @method('DELETE')
                                <button onclick="return confirm('Blog silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Reddedilen yazı yok.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

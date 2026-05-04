@extends('layouts.admin')

@section('title', 'Blog Yönetimi')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Blog Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">Kullanıcıların gönderdiği blog yazılarını onaylayabilir, reddedebilir, düzenleyebilir veya silebilirsiniz.</p>

    @if(session('success'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <p class="font-semibold">Form hataları:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                                @include('admin.blogs._edit-form', ['blog' => $blog])
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
                        <div class="grid gap-4 md:grid-cols-[6rem,1fr]">
                            <div class="overflow-hidden rounded-lg border border-emerald-100 bg-transparent p-1.5">
                                @if($blog->image_path)
                                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="{{ $blog->title }}" class="h-20 w-full object-contain">
                                @else
                                    <div class="flex h-20 items-center justify-center text-[10px] text-slate-400">Görsel yok</div>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->approved_at?->format('d.m.Y H:i') ?? '-' }}</p>
                                <p class="text-sm font-semibold text-slate-900">{{ $blog->title }}</p>
                                <div class="mt-2 flex flex-wrap gap-2">
                                    <a href="{{ route('blog.show', $blog) }}" target="_blank" class="btn-secondary px-3 py-1.5 text-xs">Yayını Aç</a>
                                    <form method="post" action="{{ route('admin.blogs.reject', $blog) }}">
                                        @csrf
                                        <button class="btn-secondary px-3 py-1.5 text-xs">Reddete Çek</button>
                                    </form>
                                    <form method="post" action="{{ route('admin.blogs.destroy', $blog) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Blog silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                    </form>
                                </div>
                                @include('admin.blogs._edit-form', ['blog' => $blog])
                            </div>
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
                        <div class="grid gap-4 md:grid-cols-[6rem,1fr]">
                            <div class="overflow-hidden rounded-lg border border-rose-100 bg-transparent p-1.5">
                                @if($blog->image_path)
                                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="{{ $blog->title }}" class="h-20 w-full object-contain">
                                @else
                                    <div class="flex h-20 items-center justify-center text-[10px] text-slate-400">Görsel yok</div>
                                @endif
                            </div>
                            <div>
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
                                @include('admin.blogs._edit-form', ['blog' => $blog])
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Reddedilen yazı yok.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

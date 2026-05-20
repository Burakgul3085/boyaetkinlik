@extends('layouts.admin')

@section('title', 'Blog Yönetimi')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Blog Yönetimi</h1>
    <p class="mt-1 text-sm text-slate-500">Kullanıcıların gönderdiği blog yazılarını onaylayabilir, reddedebilir, düzenleyebilir veya silebilirsiniz. Her yazı varsayılan olarak kapalı karttadır; detay ve görsel için kartı açın.</p>

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
    @error('blog_category')
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
    @enderror

    <section class="mt-6 space-y-4">
        @include('admin.blogs._categories-panel')
        @include('admin.blogs._admin-create')
        <div class="card p-5">
            <div class="mb-4 flex items-center justify-between gap-2">
                <h2 class="text-lg font-bold text-slate-900">Onay Bekleyenler ({{ $pendingBlogs->count() }})</h2>
            </div>
            <div class="space-y-2">
                @forelse($pendingBlogs as $blog)
                    <details
                        class="overflow-hidden rounded-2xl border border-violet-200 bg-violet-50/50 shadow-sm transition hover:border-violet-300"
                        @if(old('_edit_blog_id') == $blog->id) open @endif
                    >
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-3 marker:hidden [&::-webkit-details-marker]:hidden">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-violet-100 bg-white">
                                @if($blog->image_path)
                                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="text-[10px] text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $blog->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y H:i') }}</p>
                                <p class="mt-1 text-[11px] font-medium text-violet-700">{{ $blog->pendingCategoryLabel() }}</p>
                            </div>
                            <span class="shrink-0 rounded-lg bg-violet-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-violet-700">Aç</span>
                        </summary>
                        <div class="space-y-3 border-t border-violet-100 bg-white/90 p-4">
                            <div class="overflow-hidden rounded-xl border border-violet-100 bg-slate-50/80 p-2">
                                @if($blog->image_path)
                                    <img
                                        src="{{ asset('storage/'.$blog->image_path) }}"
                                        alt="{{ $blog->title }}"
                                        class="mx-auto max-h-48 w-full max-w-2xl object-contain"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex min-h-[5rem] items-center justify-center text-xs text-slate-400">Görsel yok</div>
                                @endif
                            </div>
                            <p class="text-sm text-slate-700">{{ $blog->excerpt }}</p>
                            <details class="rounded-xl border border-violet-100 bg-violet-50/40 p-2">
                                <summary class="cursor-pointer text-xs font-semibold text-violet-700">Metin detayını göster</summary>
                                <p class="mt-2 whitespace-pre-line text-sm text-slate-700">{{ $blog->content }}</p>
                            </details>
                            @include('admin.blogs._approve-form', ['blog' => $blog])
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="{{ route('admin.blogs.reject', $blog) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">Reddet</button>
                                </form>
                                <form method="post" action="{{ route('admin.blogs.destroy', $blog) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Blog silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                </form>
                            </div>
                            @include('admin.blogs._edit-form', ['blog' => $blog, 'blogCategories' => $blogCategories])
                        </div>
                    </details>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Bekleyen blog yazısı yok.</div>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Onaylananlar ({{ $approvedBlogs->count() }})</h2>
            <div class="mt-3 space-y-2">
                @forelse($approvedBlogs as $blog)
                    <details
                        class="overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/50 shadow-sm transition hover:border-emerald-300"
                        @if(old('_edit_blog_id') == $blog->id) open @endif
                    >
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-3 marker:hidden [&::-webkit-details-marker]:hidden">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-emerald-100 bg-white">
                                @if($blog->image_path)
                                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="text-[10px] text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $blog->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->approved_at?->format('d.m.Y H:i') ?? '-' }}</p>
                                @if($blog->category)
                                    <p class="mt-1 text-[11px] font-medium text-emerald-800">{{ $blog->category->name }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-lg bg-emerald-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-800">Aç</span>
                        </summary>
                        <div class="space-y-3 border-t border-emerald-100 bg-white/90 p-4">
                            <div class="overflow-hidden rounded-xl border border-emerald-100 bg-slate-50/80 p-2">
                                @if($blog->image_path)
                                    <img
                                        src="{{ asset('storage/'.$blog->image_path) }}"
                                        alt="{{ $blog->title }}"
                                        class="mx-auto max-h-48 w-full max-w-2xl object-contain"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex min-h-[5rem] items-center justify-center text-xs text-slate-400">Görsel yok</div>
                                @endif
                            </div>
                            <p class="line-clamp-3 text-sm text-slate-600">{{ $blog->excerpt }}</p>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('blog.show', $blog) }}" target="_blank" rel="noopener" class="btn-secondary px-3 py-1.5 text-xs">Yayını Aç</a>
                                <form method="post" action="{{ route('admin.blogs.reject', $blog) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">Reddete Çek</button>
                                </form>
                                <form method="post" action="{{ route('admin.blogs.destroy', $blog) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Blog silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                </form>
                            </div>
                            @include('admin.blogs._edit-form', ['blog' => $blog, 'blogCategories' => $blogCategories])
                        </div>
                    </details>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Henüz onaylanmış yazı yok.</div>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Reddedilenler ({{ $rejectedBlogs->count() }})</h2>
            <div class="mt-3 space-y-2">
                @forelse($rejectedBlogs as $blog)
                    <details
                        class="overflow-hidden rounded-xl border border-rose-200 bg-rose-50/50 shadow-sm transition hover:border-rose-300"
                        @if(old('_edit_blog_id') == $blog->id) open @endif
                    >
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-3 marker:hidden [&::-webkit-details-marker]:hidden">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-rose-100 bg-white">
                                @if($blog->image_path)
                                    <img src="{{ asset('storage/'.$blog->image_path) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="text-[10px] text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $blog->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $blog->authorFullName() }} · {{ $blog->created_at?->format('d.m.Y H:i') }}</p>
                            </div>
                            <span class="shrink-0 rounded-lg bg-rose-100 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-rose-800">Aç</span>
                        </summary>
                        <div class="space-y-3 border-t border-rose-100 bg-white/90 p-4">
                            <div class="overflow-hidden rounded-xl border border-rose-100 bg-slate-50/80 p-2">
                                @if($blog->image_path)
                                    <img
                                        src="{{ asset('storage/'.$blog->image_path) }}"
                                        alt="{{ $blog->title }}"
                                        class="mx-auto max-h-48 w-full max-w-2xl object-contain"
                                        loading="lazy"
                                    >
                                @else
                                    <div class="flex min-h-[5rem] items-center justify-center text-xs text-slate-400">Görsel yok</div>
                                @endif
                            </div>
                            <p class="line-clamp-3 text-sm text-slate-600">{{ $blog->excerpt }}</p>
                            @include('admin.blogs._approve-form', ['blog' => $blog])
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="{{ route('admin.blogs.destroy', $blog) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Blog silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                </form>
                            </div>
                            @include('admin.blogs._edit-form', ['blog' => $blog, 'blogCategories' => $blogCategories])
                        </div>
                    </details>
                @empty
                    <div class="rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-500">Reddedilen yazı yok.</div>
                @endforelse
            </div>
        </div>
    </section>
@endsection

@extends('layouts.admin')

@section('title', 'Deneyler')

@section('content')
    <h1 class="text-3xl font-bold text-slate-900">Deneyler</h1>
    <p class="mt-1 text-sm text-slate-500">Yalnızca yönetim panelinden deney eklenir ve yayınlanır. Ziyaretçiler yazı gönderemez.</p>

    @if(session('success'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('warning'))
        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">{{ session('warning') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @error('experiment_category')
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $message }}</div>
    @enderror

    <section class="mt-6 space-y-4">
        @include('admin.experiments._categories-panel')
        @include('admin.experiments._admin-create')

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Filtrele</h2>
            <form method="get" action="{{ route('admin.experiments.index') }}" class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <label class="block text-xs font-medium text-slate-600">
                    Durum
                    <select name="durum" class="input-ui mt-1 w-full">
                        <option value="">Tümü</option>
                        <option value="published" @selected($statusFilter === 'published')>Yayında</option>
                        <option value="draft" @selected($statusFilter === 'draft')>Taslak</option>
                    </select>
                </label>
                <label class="block text-xs font-medium text-slate-600">
                    Kategori
                    <select name="kategori" class="input-ui mt-1 w-full">
                        <option value="">Tümü</option>
                        @foreach($categoryAssignmentOptions as $opt)
                            <option value="{{ $opt['id'] }}" @selected((int) $categoryFilter === (int) $opt['id'])>
                                {{ \App\Models\ExperimentCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}
                            </option>
                        @endforeach
                    </select>
                </label>
                <label class="block text-xs font-medium text-slate-600 lg:col-span-2">
                    Ara (başlık / özet)
                    <input type="search" name="ara" value="{{ $search }}" class="input-ui mt-1 w-full" placeholder="Anahtar kelime...">
                </label>
                <div class="flex flex-wrap items-end gap-2 sm:col-span-2 lg:col-span-4">
                    <button type="submit" class="btn-primary px-4 py-2 text-sm">Uygula</button>
                    <a href="{{ route('admin.experiments.index') }}" class="btn-secondary px-4 py-2 text-sm">Sıfırla</a>
                </div>
            </form>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Yayında ({{ $publishedExperiments->count() }})</h2>
            <div class="mt-3 space-y-2">
                @forelse($publishedExperiments as $experiment)
                    <details
                        class="overflow-hidden rounded-xl border border-emerald-200 bg-emerald-50/50 shadow-sm"
                        @if(old('_edit_experiment_id') == $experiment->id) open @endif
                    >
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-3 marker:hidden [&::-webkit-details-marker]:hidden">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-emerald-100 bg-white">
                                @if($experiment->image_path)
                                    <img src="{{ asset('storage/'.$experiment->image_path) }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @elseif($experiment->youtubeThumbnailUrl())
                                    <img src="{{ $experiment->youtubeThumbnailUrl() }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                    <span class="text-[10px] text-slate-400">—</span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $experiment->title }}</p>
                                <p class="mt-0.5 text-xs text-slate-500">{{ $experiment->published_at?->format('d.m.Y H:i') ?? '-' }}</p>
                                @if($experiment->category)
                                    <p class="mt-1 text-[11px] font-medium text-emerald-800">{{ $experiment->category->name }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-lg bg-emerald-100 px-2 py-1 text-[10px] font-semibold uppercase text-emerald-800">Aç</span>
                        </summary>
                        <div class="space-y-3 border-t border-emerald-100 bg-white/90 p-4">
                            @if($experiment->youtube_url)
                                <p class="text-xs text-slate-600"><strong>YouTube:</strong> {{ $experiment->youtube_url }}</p>
                            @endif
                            <p class="text-sm text-slate-700">{{ $experiment->excerpt }}</p>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('experiments.show', $experiment) }}" target="_blank" rel="noopener" class="btn-secondary px-3 py-1.5 text-xs">Sitede gör</a>
                                <form method="post" action="{{ route('admin.experiments.unpublish', $experiment) }}">
                                    @csrf
                                    <button type="submit" class="btn-secondary px-3 py-1.5 text-xs">Yayından kaldır</button>
                                </form>
                                <form method="post" action="{{ route('admin.experiments.destroy', $experiment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Deney silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                </form>
                            </div>
                            @include('admin.experiments._edit-form', ['experiment' => $experiment])
                        </div>
                    </details>
                @empty
                    <p class="text-sm text-slate-500">Filtreye uygun yayında deney yok.</p>
                @endforelse
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-bold text-slate-900">Taslak ({{ $draftExperiments->count() }})</h2>
            <div class="mt-3 space-y-2">
                @forelse($draftExperiments as $experiment)
                    <details
                        class="overflow-hidden rounded-xl border border-amber-200 bg-amber-50/50 shadow-sm"
                        @if(old('_edit_experiment_id') == $experiment->id) open @endif
                    >
                        <summary class="flex cursor-pointer list-none items-center gap-3 p-3 marker:hidden [&::-webkit-details-marker]:hidden">
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-slate-900">{{ $experiment->title }}</p>
                                @if($experiment->category)
                                    <p class="mt-1 text-[11px] font-medium text-amber-800">{{ $experiment->category->name }}</p>
                                @endif
                            </div>
                            <span class="shrink-0 rounded-lg bg-amber-100 px-2 py-1 text-[10px] font-semibold uppercase text-amber-800">Taslak</span>
                        </summary>
                        <div class="space-y-3 border-t border-amber-100 bg-white/90 p-4">
                            <div class="flex flex-wrap gap-2">
                                <form method="post" action="{{ route('admin.experiments.publish', $experiment) }}">
                                    @csrf
                                    <button type="submit" class="btn-primary px-3 py-1.5 text-xs">Yayınla</button>
                                </form>
                                <form method="post" action="{{ route('admin.experiments.destroy', $experiment) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('Deney silinsin mi?')" class="btn-danger px-3 py-1.5 text-xs">Sil</button>
                                </form>
                            </div>
                            @include('admin.experiments._edit-form', ['experiment' => $experiment])
                        </div>
                    </details>
                @empty
                    <p class="text-sm text-slate-500">Taslak deney yok.</p>
                @endforelse
            </div>
        </div>
    </section>
@endsection

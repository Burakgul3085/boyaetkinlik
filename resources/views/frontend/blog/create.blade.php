@extends('layouts.app')

@section('title', 'Blog Yazısı Gönder')

@section('content')
    <section class="overflow-hidden rounded-3xl border border-violet-100 bg-gradient-to-br from-violet-50 via-fuchsia-50/80 to-indigo-50 p-6 shadow-sm md:p-8">
        <p class="inline-flex items-center rounded-full bg-white/85 px-3 py-1 text-xs font-semibold text-violet-700 shadow-sm">Topluluğa Katılın</p>
        <h1 class="mt-4 text-3xl font-bold tracking-tight text-slate-900 md:text-4xl">Blog Yazısı Gönder</h1>
        <p class="mt-3 max-w-3xl text-sm leading-relaxed text-slate-600 md:text-base">
            Yazınızı başlık, kısa açıklama, detay metin ve görselle birlikte gönderin. İç içe kategorilerden uygun olanı seçin veya listede yoksa yeni kategori önerin. Admin onayı sonrası yayınlanır.
        </p>
    </section>

    @if(session('success'))
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif

    <section class="mt-6">
        <form
            method="post"
            action="{{ route('blog.store') }}"
            enctype="multipart/form-data"
            class="rounded-2xl border border-violet-100 bg-white p-5 shadow-sm md:p-6"
            @submit="validateCategory($event)"
        >
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

            <div
                class="mt-6 rounded-2xl border border-violet-100 bg-violet-50/40 p-4"
                x-data="{
                    selectedCategory: @js((string) old('blog_category_id', '')),
                    suggested: @js((string) old('suggested_category_name', '')),
                    showCategoryWarning: false,
                    hasListChoice() {
                        return String(this.selectedCategory).trim() !== '';
                    },
                    hasSuggestion() {
                        return String(this.suggested).trim() !== '';
                    },
                    listLocked() {
                        return this.hasSuggestion();
                    },
                    suggestLocked() {
                        return this.hasListChoice();
                    },
                    canSubmitCategory() {
                        return this.hasListChoice() || this.hasSuggestion();
                    },
                    onCategoryChange() {
                        if (this.hasListChoice()) {
                            this.suggested = '';
                        }
                        this.showCategoryWarning = false;
                    },
                    onSuggestionInput() {
                        if (this.hasSuggestion()) {
                            this.selectedCategory = '';
                        }
                        this.showCategoryWarning = false;
                    },
                    validateCategory(event) {
                        if (!this.canSubmitCategory()) {
                            event.preventDefault();
                            this.showCategoryWarning = true;
                            this.$refs.categoryBlock?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                    }
                }"
                x-ref="categoryBlock"
            >
                <p class="text-sm font-semibold text-slate-800">Blog kategorisi *</p>
                <p class="mt-1 text-xs text-slate-500">
                    Listeden <strong>bir</strong> kategori seçin <strong>veya</strong> yeni kategori önerin; en az biri zorunludur (ikisi birden değil).
                </p>

                @error('blog_category_id')
                    <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <p
                    x-show="showCategoryWarning"
                    x-cloak
                    class="mt-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-medium text-amber-900"
                    role="alert"
                >
                    Lütfen listeden bir kategori seçin veya yeni kategori adı yazın. Kategori olmadan onaya gönderemezsiniz.
                </p>

                @if($categoryOptions->isEmpty())
                    <p class="mt-3 text-sm text-amber-800">Henüz yayınlanmış kategori yok; lütfen aşağıdan kategori önerin.</p>
                @else
                    <label class="mt-3 block text-sm font-medium" :class="listLocked() ? 'text-slate-400' : 'text-slate-700'">
                        Kategori seçin
                        <select
                            name="blog_category_id"
                            x-model="selectedCategory"
                            @change="onCategoryChange()"
                            :disabled="listLocked()"
                            class="input-ui mt-2 w-full disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <option value="">— Seçin —</option>
                            @foreach($categoryOptions as $opt)
                                <option value="{{ $opt['id'] }}">
                                    {{ \App\Models\BlogCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}
                                </option>
                            @endforeach
                        </select>
                        <span x-show="listLocked()" x-cloak class="mt-1 block text-[11px] text-slate-400">Yeni kategori yazdığınız için liste pasif.</span>
                    </label>
                @endif

                <label class="mt-4 block text-sm font-medium" :class="suggestLocked() ? 'text-slate-400' : 'text-slate-700'">
                    Kategoriniz listede yok mu? (yeni kategori önerin)
                    <input
                        type="text"
                        name="suggested_category_name"
                        x-model="suggested"
                        @input="onSuggestionInput()"
                        :disabled="suggestLocked()"
                        class="input-ui mt-2 disabled:cursor-not-allowed disabled:opacity-50"
                        placeholder="Örn: Okul öncesi etkinlik fikirleri"
                    >
                    <span x-show="suggestLocked()" x-cloak class="mt-1 block text-[11px] text-slate-400">Listeden kategori seçtiğiniz için bu alan pasif.</span>
                </label>
                <p class="mt-2 text-xs text-slate-500">Öneri yazarsanız admin onayında adı düzenlenebilir; onaylanınca kategori listesine eklenir.</p>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <button type="submit" class="btn-primary">Blogu Gönder</button>
                <a href="{{ route('blog.index') }}" class="btn-secondary">Blog Sayfasına Dön</a>
            </div>
        </form>
    </section>
@endsection

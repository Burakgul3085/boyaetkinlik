<div class="card p-5">
    <h2 class="text-lg font-bold text-slate-900">Yeni deney</h2>
    <p class="mt-1 text-sm text-slate-500">Yalnızca admin yayınlar. «Yayınla» ile /deneyler sayfasında görünür; «Taslak» ile kayıt saklanır.</p>

    <form method="post" action="{{ route('admin.experiments.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
        @csrf
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Kategori
            <select name="experiment_category_id" class="input-ui mt-2 w-full" required>
                <option value="">Seçin</option>
                @foreach($categoryAssignmentOptions as $opt)
                    <option value="{{ $opt['id'] }}" @selected((int) old('experiment_category_id') === (int) $opt['id'])>
                        {{ \App\Models\ExperimentCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}
                    </option>
                @endforeach
            </select>
        </label>
        <input name="title" value="{{ old('title') }}" placeholder="Başlık" class="input-ui md:col-span-2" required>
        <input name="author_first_name" value="{{ old('author_first_name', 'Boya') }}" placeholder="Yazar adı" class="input-ui" required>
        <input name="author_last_name" value="{{ old('author_last_name', 'Etkinlik') }}" placeholder="Yazar soyadı" class="input-ui" required>
        <textarea name="excerpt" rows="2" class="input-ui md:col-span-2" placeholder="Kısa açıklama" required>{{ old('excerpt') }}</textarea>
        <textarea name="content" rows="6" class="input-ui md:col-span-2" placeholder="Detay metin" required>{{ old('content') }}</textarea>
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            YouTube linki (opsiyonel)
            <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" placeholder="https://www.youtube.com/watch?v=..." class="input-ui mt-2 w-full">
            <span class="mt-1 block text-[11px] text-slate-500">Detay sayfasında gömülü video önizlemesi gösterilir.</span>
        </label>
        <label class="input-ui md:col-span-2">
            Görsel (opsiyonel)
            <input type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 w-full text-sm">
        </label>
        <div class="flex flex-wrap gap-2 md:col-span-2">
            <button type="submit" name="publish_now" value="1" class="btn-primary">Yayınla</button>
            <button type="submit" name="publish_now" value="0" class="btn-secondary">Taslak kaydet</button>
        </div>
    </form>
</div>

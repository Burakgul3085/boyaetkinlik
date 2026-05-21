<details class="mt-2 rounded-xl border border-violet-100 bg-white/80">
    <summary class="cursor-pointer rounded-xl px-3 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-50">Düzenle / Güncelle</summary>
    <form method="post" action="{{ route('admin.experiments.update', $experiment) }}" enctype="multipart/form-data" class="space-y-3 p-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="_edit_experiment_id" value="{{ $experiment->id }}">
        <div class="grid gap-3 md:grid-cols-2">
            <label class="block text-xs font-medium text-slate-600">
                İsim
                <input name="author_first_name" value="{{ $experiment->author_first_name }}" required class="input-ui mt-1 w-full">
            </label>
            <label class="block text-xs font-medium text-slate-600">
                Soyisim
                <input name="author_last_name" value="{{ $experiment->author_last_name }}" required class="input-ui mt-1 w-full">
            </label>
            @if($experiment->status === 'published')
                <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                    Kategori
                    <select name="experiment_category_id" class="input-ui mt-1 w-full" required>
                        @foreach($categoryAssignmentOptions as $opt)
                            @php($catModel = $experimentCategories->firstWhere('id', $opt['id']))
                            @if($catModel && ($catModel->is_active || (int) $catModel->id === (int) $experiment->experiment_category_id))
                                <option
                                    value="{{ $opt['id'] }}"
                                    @selected((int) old('experiment_category_id', $experiment->experiment_category_id) === (int) $opt['id'])
                                >{{ \App\Models\ExperimentCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}{{ $catModel->is_active ? '' : ' (pasif)' }}</option>
                            @endif
                        @endforeach
                    </select>
                </label>
            @endif
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Başlık
                <input name="title" value="{{ $experiment->title }}" required class="input-ui mt-1 w-full">
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Kısa Açıklama
                <textarea name="excerpt" required rows="2" class="input-ui mt-1 w-full">{{ $experiment->excerpt }}</textarea>
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Detay
                <textarea name="content" required rows="6" class="input-ui mt-1 w-full">{{ $experiment->content }}</textarea>
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                YouTube (opsiyonel)
                <input type="url" name="youtube_url" value="{{ $experiment->youtube_url }}" class="input-ui mt-1 w-full" placeholder="https://youtu.be/...">
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Yeni görsel (opsiyonel)
                <input type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="input-ui mt-1 w-full">
            </label>
            @if($experiment->image_path)
                <label class="inline-flex items-center gap-2 text-xs font-medium text-rose-600 md:col-span-2">
                    <input type="hidden" name="remove_image" value="0">
                    <input type="checkbox" name="remove_image" value="1">
                    Mevcut görseli kaldır
                </label>
            @endif
            @include('partials.experiment-online-lab-fields', [
                'experiment' => $experiment,
                'onlineLabTypes' => $onlineLabTypes ?? \App\Support\OnlineExperimentLab::types(),
            ])
        </div>
        <button class="btn-primary px-3 py-1.5 text-xs">Güncellemeyi Kaydet</button>
    </form>
</details>

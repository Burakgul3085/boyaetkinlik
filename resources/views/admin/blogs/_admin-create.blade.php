<div class="card p-5">
    <h2 class="text-lg font-bold text-slate-900">Admin: Yeni blog yayınla</h2>
    <p class="mt-1 text-sm text-slate-500">Onay beklemeden doğrudan blog sayfasında yayınlanır.</p>

    <form method="post" action="{{ route('admin.blogs.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
        @csrf
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Kategori
            <select name="blog_category_id" class="input-ui mt-2 w-full" required>
                <option value="">Seçin</option>
                @foreach($categoryAssignmentOptions as $opt)
                    <option value="{{ $opt['id'] }}" @selected((int) old('blog_category_id') === (int) $opt['id'])>
                        {{ \App\Models\BlogCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}
                    </option>
                @endforeach
            </select>
        </label>
        <input name="title" value="{{ old('title') }}" placeholder="Başlık" class="input-ui md:col-span-2" required>
        <input name="author_first_name" value="{{ old('author_first_name', 'Boya') }}" placeholder="Yazar adı" class="input-ui" required>
        <input name="author_last_name" value="{{ old('author_last_name', 'Etkinlik') }}" placeholder="Yazar soyadı" class="input-ui" required>
        <textarea name="excerpt" rows="2" class="input-ui md:col-span-2" placeholder="Kısa açıklama" required>{{ old('excerpt') }}</textarea>
        <textarea name="content" rows="6" class="input-ui md:col-span-2" placeholder="Detay metin" required>{{ old('content') }}</textarea>
        <label class="input-ui md:col-span-2">
            Görsel (opsiyonel)
            <input type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="mt-1 w-full text-sm">
        </label>
        <button class="btn-primary md:col-span-2">Yayınla</button>
    </form>
</div>

<details class="mt-2 rounded-xl border border-violet-100 bg-white/80">
    <summary class="cursor-pointer rounded-xl px-3 py-2 text-xs font-semibold text-violet-700 hover:bg-violet-50">Düzenle / Güncelle</summary>
    <form method="post" action="{{ route('admin.blogs.update', $blog) }}" enctype="multipart/form-data" class="space-y-3 p-3">
        @csrf
        @method('PUT')
        <input type="hidden" name="_edit_blog_id" value="{{ $blog->id }}">
        <div class="grid gap-3 md:grid-cols-2">
            <label class="block text-xs font-medium text-slate-600">
                İsim
                <input name="author_first_name" value="{{ $blog->author_first_name }}" required class="input-ui mt-1 w-full">
            </label>
            <label class="block text-xs font-medium text-slate-600">
                Soyisim
                <input name="author_last_name" value="{{ $blog->author_last_name }}" required class="input-ui mt-1 w-full">
            </label>
            @if($blog->status === 'approved' && isset($blogCategories))
                <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                    Kategori
                    <select name="blog_category_id" class="input-ui mt-1 w-full" required>
                        @foreach($blogCategories as $cat)
                            @if($cat->is_active || (int) $cat->id === (int) $blog->blog_category_id)
                                <option
                                    value="{{ $cat->id }}"
                                    @selected((int) old('blog_category_id', $blog->blog_category_id) === (int) $cat->id)
                                >{{ $cat->name }}{{ $cat->is_active ? '' : ' (pasif)' }}</option>
                            @endif
                        @endforeach
                    </select>
                    <span class="mt-1 block text-[11px] text-slate-500">Yayında olan yazının kategorisini değiştirmek için seçip kaydedin.</span>
                </label>
                @error('blog_category_id')
                    <p class="text-xs text-rose-600 md:col-span-2">{{ $message }}</p>
                @enderror
            @endif
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Başlık
                <input name="title" value="{{ $blog->title }}" required class="input-ui mt-1 w-full">
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Kısa Açıklama
                <textarea name="excerpt" required rows="2" class="input-ui mt-1 w-full">{{ $blog->excerpt }}</textarea>
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Detay Açıklama
                <textarea name="content" required rows="6" class="input-ui mt-1 w-full">{{ $blog->content }}</textarea>
            </label>
            <label class="block text-xs font-medium text-slate-600 md:col-span-2">
                Yeni Fotoğraf (opsiyonel - mevcudu değiştirir)
                <input type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="input-ui mt-1 w-full">
            </label>
            @if($blog->image_path)
                <label class="inline-flex items-center gap-2 text-xs font-medium text-rose-600 md:col-span-2">
                    <input type="hidden" name="remove_image" value="0">
                    <input type="checkbox" name="remove_image" value="1">
                    Mevcut fotoğrafı kaldır
                </label>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button class="btn-primary px-3 py-1.5 text-xs">Güncellemeyi Kaydet</button>
            <span class="text-[11px] text-slate-500">Yeni fotoğraf yüklemezsen mevcut görsel korunur.</span>
        </div>
    </form>
</details>

<form method="post" action="{{ route('admin.blogs.approve', $blog) }}" class="rounded-xl border border-violet-100 bg-violet-50/50 p-3 space-y-3">
    @csrf
    <p class="text-xs font-semibold text-violet-800">Onay — kategori</p>
    <p class="text-xs text-slate-600">Mevcut: <strong>{{ $blog->pendingCategoryLabel() }}</strong></p>

    <label class="block text-xs font-medium text-slate-700">
        Listeden kategori (varsa)
        <select name="blog_category_id" class="input-ui mt-1 w-full">
            <option value="">— Seçmeyin / öneriyi kullanın —</option>
            @foreach($activeCategories as $cat)
                <option value="{{ $cat->id }}" @selected(old('blog_category_id', $blog->blog_category_id) == $cat->id)>{{ $cat->name }}</option>
            @endforeach
        </select>
    </label>

    <label class="block text-xs font-medium text-slate-700">
        Kategori adı (düzenlenebilir — öneri veya yeni)
        <input
            type="text"
            name="category_name"
            value="{{ old('category_name', $blog->suggested_category_name) }}"
            class="input-ui mt-1 w-full"
            placeholder="Ziyaretçinin önerdiği veya yeni kategori adı"
        >
    </label>
    <p class="text-[11px] text-slate-500">Listeden seçerseniz o kullanılır. Boş bırakıp sadece seçim de yapabilirsiniz. Öneri varsa onayda düzenleyebilirsiniz; yeni kategori oluşturulur.</p>

    <button type="submit" class="btn-primary px-3 py-1.5 text-xs">Onayla ve yayınla</button>
</form>

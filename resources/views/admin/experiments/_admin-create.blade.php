@php
    $authorFirstDefault = filled(old('author_first_name')) ? old('author_first_name') : 'Boya';
    $authorLastDefault = filled(old('author_last_name')) ? old('author_last_name') : 'Etkinlik';
@endphp
<div class="card p-5">
    <h2 class="text-lg font-bold text-slate-900">Yeni deney yazısı</h2>
    <p class="mt-1 text-sm text-slate-500">Yalnızca metin, görsel ve video. «Yayınla» ile /deneyler sayfasında görünür. Online laboratuvar ayrıdır; admin panelden yönetilmez.</p>

    @if($errors->any())
        <div class="mt-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-semibold">Eksik veya hatalı alanlar:</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ route('admin.experiments.store') }}" enctype="multipart/form-data" class="mt-4 grid gap-3 md:grid-cols-2">
        @csrf
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Kategori <span class="text-rose-600">*</span>
            <select name="experiment_category_id" class="input-ui mt-2 w-full" required>
                <option value="">Seçin</option>
                @foreach($categoryAssignmentOptions as $opt)
                    <option value="{{ $opt['id'] }}" @selected((int) old('experiment_category_id') === (int) $opt['id'])>
                        {{ \App\Models\ExperimentCategory::adminSelectOptionLabel($opt['depth'], $opt['name']) }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Başlık <span class="text-rose-600">*</span>
            <input name="title" value="{{ old('title') }}" placeholder="Örn: Evde Gökkuşağı Deneyi" class="input-ui mt-2 w-full" required>
        </label>
        <label class="block text-sm font-medium text-slate-700">
            Yazar adı <span class="text-rose-600">*</span>
            <input name="author_first_name" value="{{ $authorFirstDefault }}" class="input-ui mt-2 w-full" required autocomplete="off">
        </label>
        <label class="block text-sm font-medium text-slate-700">
            Yazar soyadı <span class="text-rose-600">*</span>
            <input name="author_last_name" value="{{ $authorLastDefault }}" class="input-ui mt-2 w-full" required autocomplete="off">
        </label>
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Kısa açıklama <span class="text-rose-600">*</span>
            <textarea name="excerpt" rows="2" class="input-ui mt-2 w-full" placeholder="Liste ve detayda görünen özet" required>{{ old('excerpt') }}</textarea>
        </label>
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Detay metin <span class="text-rose-600">*</span>
            <textarea name="content" rows="6" class="input-ui mt-2 w-full" placeholder="Malzemeler, adımlar, güvenlik notları" required>{{ old('content') }}</textarea>
        </label>
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            YouTube linki (opsiyonel)
            <input type="url" name="youtube_url" value="{{ old('youtube_url') }}" placeholder="https://www.youtube.com/watch?v=..." class="input-ui mt-2 w-full">
        </label>
        <label class="block text-sm font-medium text-slate-700 md:col-span-2">
            Görsel (opsiyonel)
            <input type="file" name="image_file" accept=".png,.jpg,.jpeg,.webp" class="input-ui mt-2 w-full text-sm">
        </label>
        <div class="flex flex-wrap gap-2 md:col-span-2">
            <button type="submit" name="publish_now" value="1" class="btn-primary">Yayınla</button>
            <button type="submit" name="publish_now" value="0" class="btn-secondary">Taslak kaydet</button>
        </div>
    </form>
</div>

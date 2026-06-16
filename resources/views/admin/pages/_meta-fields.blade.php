@php
    $pageModel = isset($page) && $page instanceof \App\Models\ColoringPage ? $page : null;

    $metaAgeGroup = old('age_group', '');
    $metaLearningOutcomes = old('learning_outcomes', '');
    $metaUsageInstructions = old('usage_instructions', '');
    $metaTeacherNote = old('teacher_note', '');
    $metaFileInfo = old('file_info', '');
    $metaCopyrightNote = old('copyright_note', '');

    if ($pageModel !== null) {
        $metaAgeGroup = old('age_group', (string) $pageModel->age_group);
        $metaLearningOutcomes = old('learning_outcomes', (string) $pageModel->learning_outcomes);
        $metaUsageInstructions = old('usage_instructions', (string) $pageModel->usage_instructions);
        $metaTeacherNote = old('teacher_note', (string) $pageModel->teacher_note);
        $metaFileInfo = old('file_info', (string) $pageModel->file_info);
        $metaCopyrightNote = old('copyright_note', (string) $pageModel->copyright_note);
    }
@endphp

<div class="md:col-span-2 rounded-xl border border-violet-100 bg-violet-50/30 p-4">
    <p class="text-sm font-semibold text-slate-900">İçerik bilgileri</p>
    <p class="mt-1 text-xs text-slate-500">Bu alanlar ürün sayfasında renkli bilgi kartları olarak gösterilir. Boş bırakılanlar gizlenir.</p>
    <div class="mt-3 grid gap-3 md:grid-cols-2">
        <label class="block text-xs font-medium text-slate-600 md:col-span-2">
            Yaş grubu
            <input
                type="text"
                name="age_group"
                value="{{ $metaAgeGroup }}"
                class="input-ui mt-1"
                placeholder="Örn: 4-7 yaş"
            >
        </label>
        <label class="block text-xs font-medium text-slate-600 md:col-span-2">
            Kazanımlar
            <textarea
                name="learning_outcomes"
                rows="2"
                class="input-ui mt-1"
                placeholder="Örn: ince motor becerisi, el-göz koordinasyonu, hayvanları tanıma"
            >{{ $metaLearningOutcomes }}</textarea>
        </label>
        <label class="block text-xs font-medium text-slate-600 md:col-span-2">
            Nasıl kullanılır
            <textarea
                name="usage_instructions"
                rows="2"
                class="input-ui mt-1"
                placeholder="Örn: indir, yazdır, boya, kes, lastik tak"
            >{{ $metaUsageInstructions }}</textarea>
        </label>
        <label class="block text-xs font-medium text-slate-600 md:col-span-2">
            Öğretmen notu
            <textarea
                name="teacher_note"
                rows="2"
                class="input-ui mt-1"
                placeholder="Örn: deniz canlıları etkinliğiyle birlikte kullanılabilir"
            >{{ $metaTeacherNote }}</textarea>
        </label>
        <label class="block text-xs font-medium text-slate-600">
            Dosya bilgisi
            <input
                type="text"
                name="file_info"
                value="{{ $metaFileInfo }}"
                class="input-ui mt-1"
                placeholder="Örn: PDF / A4 / siyah-beyaz / yüksek çözünürlük"
            >
        </label>
        <label class="block text-xs font-medium text-slate-600">
            Telif notu
            <input
                type="text"
                name="copyright_note"
                value="{{ $metaCopyrightNote }}"
                class="input-ui mt-1"
                placeholder="Örn: Bu içerik BoyaEtkinlik ekibi tarafından hazırlanmıştır."
            >
        </label>
    </div>
</div>

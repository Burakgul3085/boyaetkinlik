@php
    use App\Models\Experiment;
    use App\Support\OnlineExperimentLab;

    $onlineLabTypes = $onlineLabTypes ?? OnlineExperimentLab::types();
    $expModel = (isset($experiment) && $experiment instanceof Experiment) ? $experiment : null;

    $labEnabledDefault = $expModel !== null ? (bool) $expModel->online_lab_enabled : false;
    $labTypeDefault = $expModel !== null ? (string) ($expModel->online_lab_type ?? '') : '';
    $labAgeDefault = $expModel !== null ? (string) ($expModel->online_lab_age_label ?? '5–9 yaş') : '5–9 yaş';
    $labDurationDefault = $expModel !== null ? (string) ($expModel->online_lab_duration_label ?? '5–10 dk') : '5–10 dk';
    $labSortDefault = $expModel !== null ? (int) $expModel->online_lab_sort_order : 0;
@endphp
<div class="mt-3 rounded-xl border border-indigo-100 bg-indigo-50/40 p-3 md:col-span-2">
    <p class="text-xs font-bold uppercase tracking-wide text-indigo-800">Online deney laboratuvarı</p>
    <p class="mt-1 text-[11px] text-slate-600">Açık olan deneyler <strong>/deneyler/online-dene</strong> salonunda listelenir. Dış site kullanılmaz.</p>
    <label class="mt-3 inline-flex items-center gap-2 text-sm font-medium text-slate-700">
        <input type="hidden" name="online_lab_enabled" value="0">
        <input type="checkbox" name="online_lab_enabled" value="1" @checked(old('online_lab_enabled', $labEnabledDefault))>
        Online laboratuvarda göster
    </label>
    <div class="mt-3 grid gap-3 sm:grid-cols-2">
        <label class="block text-xs font-medium text-slate-600 sm:col-span-2">
            Laboratuvar tipi
            <select name="online_lab_type" class="input-ui mt-1 w-full">
                <option value="">Seçin</option>
                @foreach($onlineLabTypes as $key => $meta)
                    <option value="{{ $key }}" @selected(old('online_lab_type', $labTypeDefault) === $key)>
                        {{ $meta['label'] }}{{ ($meta['ready'] ?? false) ? '' : ' (yakında)' }}
                    </option>
                @endforeach
            </select>
        </label>
        <label class="block text-xs font-medium text-slate-600">
            Yaş etiketi
            <input name="online_lab_age_label" value="{{ old('online_lab_age_label', $labAgeDefault) }}" class="input-ui mt-1 w-full" placeholder="5–9 yaş">
        </label>
        <label class="block text-xs font-medium text-slate-600">
            Süre etiketi
            <input name="online_lab_duration_label" value="{{ old('online_lab_duration_label', $labDurationDefault) }}" class="input-ui mt-1 w-full" placeholder="5–10 dk">
        </label>
        <label class="block text-xs font-medium text-slate-600">
            Salon sırası
            <input type="number" name="online_lab_sort_order" value="{{ old('online_lab_sort_order', $labSortDefault) }}" min="0" class="input-ui mt-1 w-full">
        </label>
    </div>
    @error('online_lab_type')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>

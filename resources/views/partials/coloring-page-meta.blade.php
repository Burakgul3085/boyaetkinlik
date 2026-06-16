@php
    $metaItems = array_values(array_filter([
        [
            'label' => 'Yaş grubu',
            'value' => trim((string) ($coloringPage->age_group ?? '')),
            'tone' => 'violet',
            'icon' => '🎂',
        ],
        [
            'label' => 'Kazanımlar',
            'value' => trim((string) ($coloringPage->learning_outcomes ?? '')),
            'tone' => 'emerald',
            'icon' => '🎯',
        ],
        [
            'label' => 'Nasıl kullanılır',
            'value' => trim((string) ($coloringPage->usage_instructions ?? '')),
            'tone' => 'sky',
            'icon' => '✨',
        ],
        [
            'label' => 'Öğretmen notu',
            'value' => trim((string) ($coloringPage->teacher_note ?? '')),
            'tone' => 'amber',
            'icon' => '📚',
        ],
        [
            'label' => 'Dosya bilgisi',
            'value' => trim((string) ($coloringPage->file_info ?? '')),
            'tone' => 'indigo',
            'icon' => '📄',
        ],
        [
            'label' => 'Telif notu',
            'value' => trim((string) ($coloringPage->copyright_note ?? '')),
            'tone' => 'rose',
            'icon' => '©️',
        ],
    ], fn (array $item) => $item['value'] !== ''));
@endphp

@if(count($metaItems) > 0)
    <section class="coloring-page-meta" aria-label="Boyama sayfası bilgileri">
        <h2 class="coloring-page-meta__title">İçerik bilgileri</h2>
        <div class="coloring-page-meta__grid">
            @foreach($metaItems as $item)
                <div class="coloring-page-meta__card coloring-page-meta__card--{{ $item['tone'] }}">
                    <div class="coloring-page-meta__card-head">
                        <span class="coloring-page-meta__icon" aria-hidden="true">{{ $item['icon'] }}</span>
                        <p class="coloring-page-meta__label">{{ $item['label'] }}</p>
                    </div>
                    <p class="coloring-page-meta__value">{{ $item['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif

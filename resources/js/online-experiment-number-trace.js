import { initTraceStudio } from './online-experiment-trace-core.js';
import { NUMBER_PATTERNS } from './trace-paths-numbers.js';

initTraceStudio({
    variant: 'number',
    patterns: NUMBER_PATTERNS,
    defaultPattern: '1',
    resetPattern: '1',
    downloadPrefix: 'sayi-calismasi',
    bgFrom: '#fffefb',
    bgTo: '#ecfdf5',
    gridColor: 'rgba(16, 185, 129, 0.1)',
    previewBg: '#ecfdf5',
    previewStroke: '#10b981',
    hintColor: '#059669',
    hintGlow: 'rgba(5, 150, 105, 0.25)',
    endColor: '#10b981',
    strokeFrom: '#22c55e',
    strokeMid: '#14b8a6',
    strokeTo: '#059669',
    celebrate: { emoji: '🔢', title: 'Mükemmel!', sub: 'Sayı çizimini başarıyla tamamladın' },
    completeHint: 'Süper! Sayı çalışması tamam 🎉',
    steps: [
        {
            title: 'Sayı çizgi stüdyosu',
            text: '0’dan 9’a kadar sayıların çizgi yollarını takip ederek sayı yazımına hazırlanırsın. Matematik ve yazı becerisi birlikte gelişir.',
            hint: 'Okul öncesi ve ilkokul için uygundur.',
            checklist: ['Sayı seç', 'Kalemi ayarla', 'Sayı yolunu tamamla'],
            sideHtml:
                '<div class="online-exp-info-box"><p class="online-exp-info-box__title">İpucu</p>' +
                '<p class="text-xs text-slate-600">Önce 1–5 ile başla, sonra 6–9’a geç. Her sayıyı birkaç kez tekrarla.</p></div>',
        },
        {
            title: 'Sayını seç',
            text: '0 ile 9 arasından bir sayı seç. Kartlarda çizim yolu önizlemesi görünür.',
            hint: 'Seçtikten sonra «Sonraki adım».',
            checklist: ['Bir sayı seçildi'],
            sideHtml: '',
        },
        {
            title: 'Kalemi hazırla',
            text: 'Kalem kalınlığını seç. Yeşil BAŞLA noktasından başlayarak sayı yolunu takip et.',
            hint: '«Çizmeye başla» ile alan açılır.',
            checklist: ['Kalem kalınlığı seçildi'],
            sideHtml: '',
        },
        {
            title: 'Sayı yolunu çiz',
            text: 'Gri çizginin üzerinden geç. İlerleme %88 olunca sayı tamamlanmış olur.',
            hint: '8 ve 9 biraz daha zor — sabırlı ol.',
            checklist: ['Sayı yolu tamamlandı'],
            sideHtml: '',
        },
    ],
    resultCopy: {
        title: 'Sayı çalışması tamam!',
        text: 'Sayıyı başarıyla çizdin. Defterde aynı sayıyı tekrar yazmak sayıları ezberlemene yardım eder.',
        hint: 'Başka sayı dene veya boyama sayfalarına git.',
    },
});

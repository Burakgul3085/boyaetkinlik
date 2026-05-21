import { initTraceStudio } from './online-experiment-trace-core.js';
import { buildNumberPatterns } from './trace-font-builder.js';

const STUDIO_CONFIG = {
    variant: 'number',
    defaultPattern: '1',
    resetPattern: '1',
    downloadPrefix: 'sayi-calismasi',
    useFontPreview: true,
    patternsInCanvasSpace: true,
    fontFamily: '"Nunito Sans Trace", "Nunito Sans", system-ui, sans-serif',
    bgFrom: '#fffefb',
    bgTo: '#ecfdf5',
    gridColor: 'rgba(16, 185, 129, 0.08)',
    previewBg: '#ecfdf5',
    previewStroke: '#059669',
    hintColor: '#059669',
    hintGlow: 'rgba(5, 150, 105, 0.22)',
    endColor: '#10b981',
    strokeFrom: '#22c55e',
    strokeMid: '#14b8a6',
    strokeTo: '#059669',
    celebrate: { emoji: '🔢', title: 'Mükemmel!', sub: 'Sayı çizimini başarıyla tamamladın' },
    completeHint: 'Süper! Sayı çalışması tamam 🎉',
    steps: [
        {
            title: 'Sayı çizgi stüdyosu',
            text: '0–9 rakamlarının normal şekillerinin üzerinden geçerek sayı yazımına hazırlanırsın.',
            hint: 'Okul öncesi ve ilkokul için uygundur.',
            checklist: ['Sayı seç', 'Kalemi ayarla', 'Sayı yolunu tamamla'],
            sideHtml:
                '<div class="online-exp-info-box"><p class="online-exp-info-box__title">İpucu</p>' +
                '<p class="text-xs text-slate-600">Önce 1–5 ile başla, sonra 6–9’a geç. Her sayıyı birkaç kez tekrarla.</p></div>',
        },
        {
            title: 'Sayını seç',
            text: '0 ile 9 arasından bir sayı seç. Kartlarda normal rakam şekli görünür.',
            hint: 'Seçtikten sonra «Sonraki adım».',
            checklist: ['Bir sayı seçildi'],
            sideHtml: '',
        },
        {
            title: 'Kalemi hazırla',
            text: 'Kalem kalınlığını seç. Yeşil BAŞLA noktasından başlayarak rakam çizgisini takip et.',
            hint: '«Çizmeye başla» ile alan açılır.',
            checklist: ['Kalem kalınlığı seçildi'],
            sideHtml: '',
        },
        {
            title: 'Sayı yolunu çiz',
            text: 'Gri kesikli çizginin üzerinden geç. İlerleme %88 olunca sayı tamamlanmış olur.',
            hint: '8 ve 9 biraz daha zor — sabırlı ol.',
            checklist: ['Sayı yolu tamamlandı'],
            sideHtml: '',
        },
    ],
    resultCopy: {
        title: 'Sayı çalışması tamam!',
        text: 'Sayıyı başarıyla çizdin. Defterde tekrar yazmak sayıları ezberlemene yardım eder.',
        hint: 'Başka sayı dene veya boyama sayfalarına git.',
    },
};

const app = document.getElementById('online-exp-app');
if (app) {
    const stage = document.getElementById('exp-stage-inner');
    if (stage) stage.classList.add('online-exp-trace-loading');

    buildNumberPatterns()
        .then((patterns) => {
            if (stage) stage.classList.remove('online-exp-trace-loading');
            initTraceStudio({ ...STUDIO_CONFIG, patterns });
        })
        .catch(() => {
            if (stage) stage.classList.remove('online-exp-trace-loading');
            const hint = document.getElementById('exp-stage-hint');
            if (hint) hint.textContent = 'Sayılar yüklenemedi. Sayfayı yenileyin.';
        });
}

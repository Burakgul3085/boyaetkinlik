import { initTraceStudio } from './online-experiment-trace-core.js';
import { buildLetterPatterns } from './trace-font-builder.js';

const STUDIO_CONFIG = {
    variant: 'letter',
    defaultPattern: 'A',
    resetPattern: 'A',
    downloadPrefix: 'harf-calismasi',
    useFontPreview: true,
    fontFamily: '"Nunito Sans Trace", "Nunito Sans", system-ui, sans-serif',
    bgFrom: '#fffefb',
    bgTo: '#eef2ff',
    gridColor: 'rgba(99, 102, 241, 0.08)',
    previewBg: '#eef2ff',
    previewStroke: '#4f46e5',
    hintColor: '#4f46e5',
    hintGlow: 'rgba(79, 70, 229, 0.22)',
    endColor: '#6366f1',
    strokeFrom: '#22c55e',
    strokeMid: '#3b82f6',
    strokeTo: '#6366f1',
    celebrate: { emoji: '🔤', title: 'Süper!', sub: 'Harf çizimini başarıyla tamamladın' },
    completeHint: 'Harika! Harf çalışması tamam 🎉',
    steps: [
        {
            title: 'Harf çizgi stüdyosu',
            text: 'Gerçek harf şekillerinin üzerinden geçerek yazmaya hazırlanırsın. Okuma-yazma öncesi motor beceri gelişir.',
            hint: 'Okul öncesi ve ilkokul 1. sınıf için idealdir.',
            checklist: ['Harf seç', 'Kalemi ayarla', 'Harf yolunu tamamla'],
            sideHtml:
                '<div class="online-exp-info-box"><p class="online-exp-info-box__title">İpucu</p>' +
                '<p class="text-xs text-slate-600">Ç, Ğ, İ, Ö, Ş, Ü harflerine dikkat et. Yavaş ve sabırlı çiz.</p></div>',
        },
        {
            title: 'Harfini seç',
            text: 'Alfabeden bir harf seç. Kartlarda normal harf şekli görünür.',
            hint: 'Seçtikten sonra «Sonraki adım».',
            checklist: ['Bir harf seçildi'],
            sideHtml: '',
        },
        {
            title: 'Kalemi hazırla',
            text: 'İnce, orta veya kalın kalem seç. Yeşil BAŞLA noktasından başlayıp harf çizgisini takip et.',
            hint: '«Çizmeye başla» ile alan açılır.',
            checklist: ['Kalem kalınlığı seçildi'],
            sideHtml: '',
        },
        {
            title: 'Harf yolunu çiz',
            text: 'Gri kesikli çizginin üzerinden geç. İlerleme halkası %88 dolunca harf tamamlanmış sayılır.',
            hint: 'Çizgi sırasına uyarak ilerle.',
            checklist: ['Harf yolu tamamlandı'],
            sideHtml: '',
        },
    ],
    resultCopy: {
        title: 'Harf çalışması tamam!',
        text: 'Harfi başarıyla çizdin. Defterde tekrar etmek yazmayı kalıcı öğrenmene yardım eder.',
        hint: 'Başka harf dene veya boyama sayfalarına geç.',
    },
};

const app = document.getElementById('online-exp-app');
if (app) {
    const stage = document.getElementById('exp-stage-inner');
    if (stage) stage.classList.add('online-exp-trace-loading');

    buildLetterPatterns()
        .then((patterns) => {
            if (stage) stage.classList.remove('online-exp-trace-loading');
            initTraceStudio({ ...STUDIO_CONFIG, patterns });
        })
        .catch(() => {
            if (stage) stage.classList.remove('online-exp-trace-loading');
            const hint = document.getElementById('exp-stage-hint');
            if (hint) hint.textContent = 'Harfler yüklenemedi. Sayfayı yenileyin.';
        });
}

import { initTraceStudio } from './online-experiment-trace-core.js';
import { LETTER_PATTERNS } from './trace-paths-letters.js';

initTraceStudio({
    variant: 'letter',
    patterns: LETTER_PATTERNS,
    defaultPattern: 'A',
    resetPattern: 'A',
    downloadPrefix: 'harf-calismasi',
    bgFrom: '#fffefb',
    bgTo: '#eef2ff',
    gridColor: 'rgba(99, 102, 241, 0.1)',
    previewBg: '#eef2ff',
    previewStroke: '#6366f1',
    hintColor: '#4f46e5',
    hintGlow: 'rgba(79, 70, 229, 0.25)',
    endColor: '#6366f1',
    strokeFrom: '#22c55e',
    strokeMid: '#3b82f6',
    strokeTo: '#6366f1',
    celebrate: { emoji: '🔤', title: 'Süper!', sub: 'Harf çizimini başarıyla tamamladın' },
    completeHint: 'Harika! Harf çalışması tamam 🎉',
    steps: [
        {
            title: 'Harf çizgi stüdyosu',
            text: 'Türkçe alfabedeki büyük harflerin çizgi yollarını takip ederek yazmaya hazırlanırsın. Okuma-yazma öncesi motor beceri gelişir.',
            hint: 'Okul öncesi ve ilkokul 1. sınıf için idealdir.',
            checklist: ['Harf seç', 'Kalemi ayarla', 'Harf yolunu tamamla'],
            sideHtml:
                '<div class="online-exp-info-box"><p class="online-exp-info-box__title">İpucu</p>' +
                '<p class="text-xs text-slate-600">Ç, Ğ, İ, Ö, Ş, Ü gibi Türkçe harflere dikkat et. Yavaş ve sabırlı çiz.</p></div>',
        },
        {
            title: 'Harfini seç',
            text: 'Alfabeden bir harf seç. Kartlarda çizim yolu önizlemesi ve zorluk etiketi görünür.',
            hint: 'Seçtikten sonra «Sonraki adım».',
            checklist: ['Bir harf seçildi'],
            sideHtml: '',
        },
        {
            title: 'Kalemi hazırla',
            text: 'İnce, orta veya kalın kalem seç. Yeşil BAŞLA noktasından başlayıp harf yolunu takip et.',
            hint: '«Çizmeye başla» ile çizim alanı açılır.',
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
        text: 'Harfi başarıyla çizdin. Gerçek kalemle defterde tekrar etmek yazmayı kalıcı öğrenmene yardım eder.',
        hint: 'Başka harf dene veya boyama sayfalarına geç.',
    },
});

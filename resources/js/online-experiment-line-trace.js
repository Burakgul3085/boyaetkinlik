import { initTraceStudio } from './online-experiment-trace-core.js';
import { SHAPE_PATTERNS } from './trace-paths-shapes.js';

initTraceStudio({
    variant: 'shape',
    patterns: SHAPE_PATTERNS,
    defaultPattern: 'ev',
    resetPattern: 'ev',
    downloadPrefix: 'cizgi-calismasi',
    celebrate: { emoji: '⭐', title: 'Harika!', sub: 'Çizgiyi başarıyla tamamladın' },
    completeHint: 'Muhteşem! Çizgi çalışması tamam 🎉',
    steps: [
        {
            title: 'Çizgi çalışması stüdyosu',
            text: 'Boya Etkinlik sayfalarındaki çizgi çalışmaları gibi: gri çizginin üzerinden geçerek deseni tamamlarsın. El-göz koordinasyonu ve kalem tutuşu gelişir.',
            hint: 'Okul öncesi, ilkokul ve ortaokul için uygundur.',
            checklist: ['Desen seç', 'Kalemi ayarla', 'Çizgiyi tamamla'],
            sideHtml:
                '<div class="online-exp-info-box"><p class="online-exp-info-box__title">İpucu</p>' +
                '<p class="text-xs text-slate-600">Yavaş ve dikkatli çiz. Tablet veya fare ile rahatça kullanılır.</p></div>',
        },
        {
            title: 'Desenini seç',
            text: 'Beş desenden birini seç. Kartlarda küçük önizleme görünür; zorluk etiketine bakabilirsin.',
            hint: 'Seçtikten sonra «Sonraki adım».',
            checklist: ['Bir desen seçildi'],
            sideHtml: '',
        },
        {
            title: 'Kalemi hazırla',
            text: 'İnce, orta veya kalın kalem kalınlığını seç. Yeşil noktadan başlayıp çizgiyi takip edeceksin; mor nokta bitiş.',
            hint: '«Çizmeye başla» ile alan açılır.',
            checklist: ['Kalem kalınlığı seçildi'],
            sideHtml: '',
        },
        {
            title: 'Çizgiyi tamamla',
            text: 'Parmağını veya fareni basılı tutarak çizginin üzerinden geç. İlerleme halkası %88 dolunca başarı!',
            hint: 'Çizgiden çok uzaklaşırsan ilerleme yavaşlar.',
            checklist: ['Çizgi tamamlandı'],
            sideHtml: '',
        },
    ],
    resultCopy: {
        title: 'Çizgi tamamlandı!',
        text: 'Çizgi çalışması başarıyla bitti. Boyama sayfalarında da çizgilerin üzerinden geçmek boyayı daha güzel gösterir.',
        hint: 'Başka desen dene, PNG kaydet veya boyama sayfalarına git.',
    },
});

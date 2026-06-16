@php
    $consentContext = $context ?? 'guest';
    $isOwner = $consentContext === 'owner';
@endphp

<div class="paint-room-consent">
    <p class="paint-room-consent__heading">
        Görüntülü boyama hizmeti — bilgilendirme ve onay metni
    </p>
    <p class="paint-room-consent__note">
        Bu metin, sitedeki genel yasal bilgilendirme ve politika sayfalarından bağımsızdır; yalnızca görüntülü boyama odası hizmeti için geçerlidir.
    </p>

    <div class="paint-room-consent__text" tabindex="0" role="region" aria-label="Görüntülü boyama hizmet şartları">
        @if($isOwner)
            <p><strong>1. Oda sahibi olarak hizmet kapsamı</strong> — Üye hesabınızla görüntülü boyama odası oluşturduğunuzda, seçtiğiniz boyama sayfası üzerinde en fazla bir (1) misafir ile eş zamanlı çalışma ve tarayıcı üzerinden görüntülü/sesli iletişim imkânı sunulur. Oda süresi ve katılımcı sayısı sistem tarafından sınırlandırılır.</p>
        @else
            <p><strong>1. Misafir olarak hizmet kapsamı</strong> — Davet linki veya PIN ile görüntülü boyama odasına katıldığınızda, oda sahibi ile aynı boyama sayfasında eş zamanlı çalışma ve tarayıcı üzerinden görüntülü/sesli iletişim imkânı sunulur. Üyelik zorunlu değildir; oturum geçicidir.</p>
        @endif

        <p><strong>2. Yaş onayı (+18)</strong> — Bu hizmeti yalnızca on sekiz (18) yaşını doldurmuş kişiler kullanabilir. Onay kutusunu işaretleyerek reşit olduğunuzu, hizmeti kendi özgür iradenizle kullandığınızı ve yaş şartını karşıladığınızı beyan edersiniz. Reşit olmayanların hizmeti kullanması kesinlikle yasaktır.</p>

        <p><strong>3. Görüntü ve ses — kayıt ve kullanım taahhüdü</strong> — Boya Etkinlik, görüntülü boyama odalarında ses ve görüntü akışını <em>kaydetmez</em>, <em>depolamaz</em>, <em>arşivlemez</em> ve sonradan <em>yeniden oynatmak</em> veya üçüncü taraflara <em>satmak, kiralamak, paylaşmak veya pazarlama amacıyla kullanmak</em> için işlemez. Sunucularımızda ses/görüntü kaydı tutulmaz; ekran, kamera veya mikrofon çıktısı platform tarafından kayıt altına alınmaz.</p>

        <p><strong>4. Teknik işleyiş</strong> — Görüntü ve ses iletişimi, mümkün olduğunda katılımcıların tarayıcıları arasında doğrudan (eşler arası) aktarılır. Oturum sırasında bağlantının kurulması için gerekli sinyal verileri geçici olarak işlenebilir; bu veriler ses/görüntü içeriğinizin kendisini oluşturmaz ve kalıcı kayıt amacı taşımaz. Oda kapandığında veya süre dolduğunda oturuma özgü geçici veriler silinir veya erişilemez hale getirilir.</p>

        <p><strong>5. Kişisel veriler</strong> — Görünen ad (isteğe bağlı), oda/PIN/davet bilgisi, oturum süresi ve hizmetin yürütülmesi için zorunlu teknik kayıtlar yalnızca hizmetin sağlanması amacıyla, ölçülü ve sınırlı süreyle işlenir. Bu metin, sitedeki genel KVKK aydınlatma metni veya gizlilik politikasının yerine geçmez; görüntülü boyama hizmetine özel ek bilgilendirmedir.</p>

        <p><strong>6. Kullanıcı sorumluluğu</strong> — Kamera ve mikrofonu açma, görüntü/ses paylaşma, sohbet içeriği ve boyama alanındaki davranışlarınızdan siz sorumlusunuz. Karşı tarafın da kamera/mikrofon kullanımı tamamen kendi tercihine bağlıdır. Hukuka aykırı, hakaret içeren, müstehcen, nefret söylemi barındıran veya üçüncü kişilerin haklarını ihlal eden kullanım yasaktır.</p>

        <p><strong>7. Üçüncü taraf kayıt yasağı</strong> — Platform kayıt yapmaz; buna rağmen katılımcıların kendi cihazlarıyla ekran görüntüsü, ekran kaydı veya harici kayıt alması teknik olarak mümkün olabilir ve bu tamamen ilgili kişinin sorumluluğundadır. Diğer katılımcıların izni olmadan kayıt almak ve paylaşmak yasaktır.</p>

        <p><strong>8. Hizmet değişikliği ve sonlandırma</strong> — Oda sahibi veya misafir istediği zaman odadan ayrılabilir. Süre dolunca veya oda kapatılınca oturum sona erer. Teknik arıza, kötüye kullanım şüphesi veya güvenlik gerekçesiyle oturum önceden sonlandırılabilir.</p>

        <p><strong>9. Onay</strong> — Aşağıdaki kutuyu işaretleyerek bu metni okuduğunuzu, anladığınızı; on sekiz (18) yaş üstü olduğunuzu; ses ve görüntünüzün platform tarafından kaydedilmediğini ve pazarlama veya kalıcı kullanım amacıyla işlenmeyeceğini; görüntülü boyama hizmetini bu şartlarda kullanmayı kabul ettiğinizi beyan edersiniz.</p>
    </div>

    <label class="paint-room-consent__check">
        <input type="hidden" name="paint_room_consent_accepted" value="0">
        <input
            type="checkbox"
            name="paint_room_consent_accepted"
            value="1"
            required
            class="paint-room-consent__checkbox"
            @checked(old('paint_room_consent_accepted'))
        >
        <span>
            Yukarıdaki görüntülü boyama bilgilendirme metnini okudum ve anladım.
            <strong>On sekiz (18) yaş üstü olduğumu</strong> beyan ederim;
            ses ve görüntümün <strong>kaydedilmeyeceğini</strong> ve platform tarafından
            <strong>kalıcı olarak kullanılmayacağını</strong> kabul ediyorum.
            @if($isOwner)
                Oda oluşturarak bu şartlarda hizmeti kullanmayı onaylıyorum.
            @else
                Odaya katılarak bu şartlarda hizmeti kullanmayı onaylıyorum.
            @endif
        </span>
    </label>
</div>

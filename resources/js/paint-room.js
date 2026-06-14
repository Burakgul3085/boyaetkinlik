/**
 * Görüntülü boyama odası — lobi + WebRTC P2P (ses/görüntü)
 */
(function () {
    const root = document.getElementById('paint-room-lobby');
    if (!root) return;

    const statusUrl = root.dataset.statusUrl;
    const signalPollUrl = root.dataset.signalPollUrl;
    const signalSendUrl = root.dataset.signalSendUrl;
    const leaveUrl = root.dataset.leaveUrl;
    const indexUrl = root.dataset.indexUrl;
    const role = root.dataset.role;
    const expiresAt = new Date(root.dataset.expiresAt);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    const countEl = document.getElementById('paint-room-count');
    const statusText = document.getElementById('paint-room-status-text');
    const timerEl = document.getElementById('paint-room-timer');
    const webrtcStatus = document.getElementById('paint-room-webrtc-status');
    const localVideo = document.getElementById('paint-room-local');
    const remoteVideo = document.getElementById('paint-room-remote');
    const startMediaBtn = document.getElementById('paint-room-start-media');
    const toggleMicBtn = document.getElementById('paint-room-toggle-mic');
    const toggleCamBtn = document.getElementById('paint-room-toggle-cam');

    const ICE_SERVERS = [{ urls: 'stun:stun.l.google.com:19302' }, { urls: 'stun:stun1.l.google.com:19302' }];

    let participantCount = parseInt(countEl?.textContent || '1', 10);
    let pc = null;
    let localStream = null;
    let lastSignalId = 0;
    let mediaStarted = false;
    let signalPollTimer = null;
    let micEnabled = true;
    let camEnabled = true;

    function setWebrtcStatus(msg) {
        if (webrtcStatus) webrtcStatus.textContent = msg;
    }

    document.querySelectorAll('[data-copy-target]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const el = document.getElementById(btn.dataset.copyTarget);
            if (!el) return;
            const text = el.tagName === 'INPUT' ? el.value : el.textContent.trim();
            navigator.clipboard?.writeText(text).then(() => {
                const prev = btn.textContent;
                btn.textContent = 'Kopyalandı ✓';
                setTimeout(() => { btn.textContent = prev; }, 1600);
            });
        });
    });

    function updateTimer() {
        const diff = Math.max(0, expiresAt.getTime() - Date.now());
        const mins = Math.floor(diff / 60000);
        const secs = Math.floor((diff % 60000) / 1000);
        timerEl.textContent = `${mins}:${String(secs).padStart(2, '0')}`;
        if (diff <= 0) window.location.href = indexUrl + '?expired=1';
    }

    async function postSignal(type, payload) {
        const res = await fetch(signalSendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
            body: JSON.stringify({ type, payload }),
        });
        if (!res.ok) throw new Error('Sinyal gönderilemedi');
        return res.json();
    }

    function createPeerConnection() {
        if (pc) {
            pc.close();
            pc = null;
        }
        pc = new RTCPeerConnection({ iceServers: ICE_SERVERS });
        pc.onicecandidate = (ev) => {
            if (ev.candidate) {
                postSignal('ice', ev.candidate.toJSON()).catch(() => {});
            }
        };
        pc.ontrack = (ev) => {
            if (remoteVideo && ev.streams[0]) {
                remoteVideo.srcObject = ev.streams[0];
                setWebrtcStatus('Görüntülü bağlantı kuruldu');
            }
        };
        pc.onconnectionstatechange = () => {
            if (!pc) return;
            if (pc.connectionState === 'connected') {
                setWebrtcStatus('Bağlandı — ses ve görüntü aktif');
            } else if (pc.connectionState === 'failed' || pc.connectionState === 'disconnected') {
                setWebrtcStatus('Bağlantı koptu — yeniden deneyin');
            }
        };
        if (localStream) {
            localStream.getTracks().forEach((t) => pc.addTrack(t, localStream));
        }
    }

    async function handleSignal(signal) {
        if (!pc && signal.type !== 'offer') return;
        try {
            if (signal.type === 'offer') {
                if (!localStream) throw new Error('not ready');
                if (!pc) createPeerConnection();
                await pc.setRemoteDescription(new RTCSessionDescription(signal.payload));
                const answer = await pc.createAnswer();
                await pc.setLocalDescription(answer);
                await postSignal('answer', answer);
            } else if (signal.type === 'answer') {
                await pc.setRemoteDescription(new RTCSessionDescription(signal.payload));
            } else if (signal.type === 'ice') {
                if (signal.payload) {
                    await pc.addIceCandidate(new RTCIceCandidate(signal.payload));
                }
            }
        } catch (_) {
            setWebrtcStatus('Bağlantı kurulurken hata oluştu');
        }
    }

    async function pollSignals() {
        try {
            const res = await fetch(`${signalPollUrl}?after=${lastSignalId}`, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            if (!res.ok) return;
            const data = await res.json();
            for (const sig of data.signals || []) {
                try {
                    await handleSignal({ type: sig.type, payload: sig.payload });
                    lastSignalId = Math.max(lastSignalId, sig.id);
                } catch (_) {
                    /* tekrar dene */
                }
            }
        } catch (_) {
            /* sessiz */
        }
    }

    function startSignalPolling() {
        if (signalPollTimer) return;
        pollSignals();
        signalPollTimer = setInterval(pollSignals, 1200);
    }

    function stopSignalPolling() {
        if (signalPollTimer) {
            clearInterval(signalPollTimer);
            signalPollTimer = null;
        }
    }

    function teardownMedia() {
        stopSignalPolling();
        if (pc) {
            pc.close();
            pc = null;
        }
        if (localStream) {
            localStream.getTracks().forEach((t) => t.stop());
            localStream = null;
        }
        if (localVideo) localVideo.srcObject = null;
        if (remoteVideo) remoteVideo.srcObject = null;
        mediaStarted = false;
        lastSignalId = 0;
        if (startMediaBtn) startMediaBtn.hidden = false;
        if (toggleMicBtn) toggleMicBtn.hidden = true;
        if (toggleCamBtn) toggleCamBtn.hidden = true;
        setWebrtcStatus('Görüntülü bağlantı sonlandı');
    }

    async function startVideoChat() {
        if (mediaStarted || participantCount < 2) return;
        mediaStarted = true;
        if (startMediaBtn) startMediaBtn.hidden = true;
        setWebrtcStatus('Kamera ve mikrofon izni isteniyor…');

        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: true,
            });
            if (localVideo) localVideo.srcObject = localStream;
            if (toggleMicBtn) toggleMicBtn.hidden = false;
            if (toggleCamBtn) toggleCamBtn.hidden = false;
            setWebrtcStatus('Medya hazır — eşleşme kuruluyor…');

            createPeerConnection();
            startSignalPolling();

            if (role === 'owner') {
                await new Promise((r) => setTimeout(r, 2000));
                const offer = await pc.createOffer();
                await pc.setLocalDescription(offer);
                await postSignal('offer', offer);
                setWebrtcStatus('Karşı taraf bekleniyor…');
            } else {
                setWebrtcStatus('Oda sahibine bağlanılıyor…');
            }
        } catch (err) {
            mediaStarted = false;
            if (startMediaBtn) startMediaBtn.hidden = false;
            const denied = err?.name === 'NotAllowedError' || err?.name === 'PermissionDeniedError';
            setWebrtcStatus(denied
                ? 'Kamera/mikrofon izni reddedildi. Tarayıcı ayarlarından izin verin.'
                : 'Kamera veya mikrofon açılamadı.');
        }
    }

    async function pollStatus() {
        try {
            const res = await fetch(statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data.open) {
                teardownMedia();
                window.location.href = indexUrl;
                return;
            }
            const prev = participantCount;
            participantCount = data.participants;
            if (countEl) countEl.textContent = String(participantCount);
            if (statusText) statusText.textContent = data.message;

            if (participantCount < 2 && prev >= 2) {
                teardownMedia();
            }
            if (participantCount >= 2 && !mediaStarted) {
                startVideoChat();
            }
        } catch (_) {
            /* sessiz */
        }
    }

    function sendLeaveBeacon() {
        if (role !== 'owner' || !leaveUrl) return;
        const body = new URLSearchParams({ _token: csrf });
        if (navigator.sendBeacon) navigator.sendBeacon(leaveUrl, body);
    }

    if (startMediaBtn) {
        startMediaBtn.addEventListener('click', () => {
            if (participantCount >= 2) startVideoChat();
            else setWebrtcStatus('Önce misafirin katılmasını bekleyin (2/2)');
        });
    }

    if (toggleMicBtn) {
        toggleMicBtn.addEventListener('click', () => {
            if (!localStream) return;
            micEnabled = !micEnabled;
            localStream.getAudioTracks().forEach((t) => { t.enabled = micEnabled; });
            toggleMicBtn.textContent = micEnabled ? 'Mikrofonu kapat' : 'Mikrofonu aç';
        });
    }

    if (toggleCamBtn) {
        toggleCamBtn.addEventListener('click', () => {
            if (!localStream) return;
            camEnabled = !camEnabled;
            localStream.getVideoTracks().forEach((t) => { t.enabled = camEnabled; });
            toggleCamBtn.textContent = camEnabled ? 'Kamerayı kapat' : 'Kamerayı aç';
        });
    }

    if (role === 'owner') {
        window.addEventListener('pagehide', () => { teardownMedia(); sendLeaveBeacon(); });
        window.addEventListener('beforeunload', () => { teardownMedia(); sendLeaveBeacon(); });
    } else {
        window.addEventListener('pagehide', teardownMedia);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
    pollStatus();
    setInterval(pollStatus, 2500);

    if (participantCount >= 2) {
        setTimeout(startVideoChat, 800);
    }
})();

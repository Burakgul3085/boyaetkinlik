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

    const ICE_SERVERS = [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
        { urls: 'stun:stun2.l.google.com:19302' },
    ];

    let participantCount = parseInt(countEl?.textContent || '1', 10);
    let pc = null;
    let localStream = null;
    let lastSignalId = 0;
    let mediaStarted = false;
    let remoteReady = false;
    let signalPollTimer = null;
    let reconnectTimer = null;
    let micEnabled = true;
    let camEnabled = true;
    const iceQueue = [];

    function setWebrtcStatus(msg) {
        if (webrtcStatus) webrtcStatus.textContent = msg;
    }

    function sdpPayload(desc) {
        return { type: desc.type, sdp: desc.sdp };
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
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ type, payload }),
        });
        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Sinyal gönderilemedi');
        }
        return res.json();
    }

    async function flushIceQueue() {
        while (iceQueue.length && pc && pc.remoteDescription) {
            const cand = iceQueue.shift();
            try {
                await pc.addIceCandidate(new RTCIceCandidate(cand));
            } catch (_) {
                /* sonraki aday */
            }
        }
    }

    function attachRemoteStream(stream) {
        if (!remoteVideo || !stream) return;
        remoteVideo.srcObject = stream;
        remoteVideo.play?.().catch(() => {});
    }

    function createPeerConnection() {
        if (pc) {
            pc.close();
            pc = null;
        }
        remoteReady = false;
        iceQueue.length = 0;

        pc = new RTCPeerConnection({ iceServers: ICE_SERVERS, iceCandidatePoolSize: 4 });

        pc.onicecandidate = (ev) => {
            if (ev.candidate) {
                postSignal('ice', ev.candidate.toJSON()).catch(() => {});
            }
        };

        pc.ontrack = (ev) => {
            const stream = ev.streams?.[0] || new MediaStream([ev.track]);
            attachRemoteStream(stream);
            setWebrtcStatus('Görüntülü bağlantı kuruldu');
        };

        pc.onconnectionstatechange = () => {
            if (!pc) return;
            const st = pc.connectionState;
            if (st === 'connected') {
                setWebrtcStatus('Bağlandı — ses ve görüntü aktif');
                if (reconnectTimer) {
                    clearInterval(reconnectTimer);
                    reconnectTimer = null;
                }
            } else if (st === 'failed') {
                setWebrtcStatus('Bağlantı kurulamadı — yeniden deneniyor…');
                scheduleReconnect();
            } else if (st === 'disconnected') {
                setWebrtcStatus('Bağlantı zayıf — yeniden deneniyor…');
            }
        };

        if (localStream) {
            localStream.getTracks().forEach((t) => pc.addTrack(t, localStream));
        }
    }

    async function applyRemoteDescription(payload) {
        await pc.setRemoteDescription(new RTCSessionDescription(payload));
        remoteReady = true;
        await flushIceQueue();
    }

    async function handleSignal(signal) {
        if (!pc && signal.type !== 'offer') return;

        if (signal.type === 'offer') {
            if (!localStream) throw new Error('not_ready');
            if (!pc || pc.connectionState !== 'connected') {
                createPeerConnection();
            }
            await applyRemoteDescription(signal.payload);
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await postSignal('answer', sdpPayload(pc.localDescription));
            setWebrtcStatus('Yanıt gönderildi — bağlanılıyor…');
            return;
        }

        if (signal.type === 'answer') {
            if (!pc) throw new Error('no_pc');
            await applyRemoteDescription(signal.payload);
            setWebrtcStatus('Karşı taraf yanıt verdi — bağlanılıyor…');
            return;
        }

        if (signal.type === 'ice') {
            if (!signal.payload?.candidate) return;
            if (!pc || !pc.remoteDescription) {
                iceQueue.push(signal.payload);
                return;
            }
            await pc.addIceCandidate(new RTCIceCandidate(signal.payload));
        }
    }

    async function pollSignals() {
        const res = await fetch(`${signalPollUrl}?after=${lastSignalId}`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (!res.ok) {
            if (res.status === 403) setWebrtcStatus('Oturum hatası — sayfayı yenileyin');
            return;
        }
        const data = await res.json();
        for (const sig of data.signals || []) {
            try {
                await handleSignal({ type: sig.type, payload: sig.payload });
                lastSignalId = Math.max(lastSignalId, sig.id);
            } catch (err) {
                if (err?.message !== 'not_ready') {
                    setWebrtcStatus('Sinyal işlenemedi, tekrar deneniyor…');
                }
            }
        }
    }

    function startSignalPolling() {
        if (signalPollTimer) return;
        pollSignals();
        signalPollTimer = setInterval(pollSignals, 700);
    }

    function stopSignalPolling() {
        if (signalPollTimer) {
            clearInterval(signalPollTimer);
            signalPollTimer = null;
        }
    }

    function teardownMedia() {
        stopSignalPolling();
        if (reconnectTimer) {
            clearInterval(reconnectTimer);
            reconnectTimer = null;
        }
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
        remoteReady = false;
        lastSignalId = 0;
        iceQueue.length = 0;
        if (startMediaBtn) startMediaBtn.classList.remove('hidden');
        if (toggleMicBtn) toggleMicBtn.classList.add('hidden');
        if (toggleCamBtn) toggleCamBtn.classList.add('hidden');
        setWebrtcStatus('Görüntülü bağlantı sonlandı');
    }

    async function sendOffer() {
        if (!pc) return;
        const offer = await pc.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: true });
        await pc.setLocalDescription(offer);
        const result = await postSignal('offer', sdpPayload(pc.localDescription));
        if (result?.id) lastSignalId = Math.max(lastSignalId, result.id - 1);
        setWebrtcStatus('Karşı taraf bekleniyor…');
    }

    function scheduleReconnect() {
        if (reconnectTimer || role !== 'owner') return;
        reconnectTimer = setInterval(async () => {
            if (!pc || pc.connectionState === 'connected') return;
            try {
                lastSignalId = 0;
                iceQueue.length = 0;
                createPeerConnection();
                startSignalPolling();
                await sendOffer();
            } catch (_) {
                /* sonraki tur */
            }
        }, 8000);
    }

    async function startVideoChat() {
        if (mediaStarted || participantCount < 2) return;
        mediaStarted = true;
        if (startMediaBtn) startMediaBtn.classList.add('hidden');
        setWebrtcStatus('Kamera ve mikrofon izni isteniyor…');

        try {
            localStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'user', width: { ideal: 640 }, height: { ideal: 480 } },
                audio: { echoCancellation: true, noiseSuppression: true },
            });
            if (localVideo) {
                localVideo.srcObject = localStream;
                localVideo.play?.().catch(() => {});
            }
            if (toggleMicBtn) toggleMicBtn.classList.remove('hidden');
            if (toggleCamBtn) toggleCamBtn.classList.remove('hidden');
            setWebrtcStatus('Medya hazır — eşleşme kuruluyor…');

            createPeerConnection();
            startSignalPolling();

            if (role === 'owner') {
                await new Promise((r) => setTimeout(r, 1500));
                await sendOffer();
                scheduleReconnect();
            } else {
                setWebrtcStatus('Oda sahibine bağlanılıyor…');
            }
        } catch (err) {
            mediaStarted = false;
            if (startMediaBtn) startMediaBtn.classList.remove('hidden');
            const denied = err?.name === 'NotAllowedError' || err?.name === 'PermissionDeniedError';
            setWebrtcStatus(denied
                ? 'Kamera/mikrofon izni reddedildi. Tarayıcı ayarlarından izin verin.'
                : 'Kamera veya mikrofon açılamadı.');
        }
    }

    async function pollStatus() {
        try {
            const res = await fetch(statusUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
            });
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

            if (participantCount < 2 && prev >= 2) teardownMedia();
            if (participantCount >= 2 && !mediaStarted) startVideoChat();
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
    } else {
        window.addEventListener('pagehide', teardownMedia);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
    pollStatus();
    setInterval(pollStatus, 2000);

    if (participantCount >= 2) setTimeout(startVideoChat, 600);
})();

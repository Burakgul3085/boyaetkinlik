/**
 * Görüntülü boyama odası — lobi + WebRTC P2P (ses/görüntü) v3
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
    const guestToken = root.dataset.guestToken || '';
    const expiresAt = new Date(root.dataset.expiresAt);
    const csrf = root.dataset.csrf
        || document.querySelector('meta[name="csrf-token"]')?.content
        || '';

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
        {
            urls: [
                'turn:openrelay.metered.ca:80',
                'turn:openrelay.metered.ca:443',
                'turns:openrelay.metered.ca:443',
            ],
            username: 'openrelayproject',
            credential: 'openrelayproject',
        },
    ];

    let participantCount = parseInt(countEl?.textContent || '1', 10);
    let pc = null;
    let localStream = null;
    let lastSignalId = 0;
    let mediaStarted = false;
    let signalPollTimer = null;
    let reconnectTimer = null;
    let guestWaitTimer = null;
    let micEnabled = true;
    let camEnabled = true;
    let signalsReceived = 0;
    const iceQueue = [];

    function authHeaders() {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (guestToken) headers['X-Paint-Room-Guest-Token'] = guestToken;
        return headers;
    }

    function setWebrtcStatus(msg) {
        if (webrtcStatus) webrtcStatus.textContent = msg;
    }

    function sdpPayload(desc) {
        if (!desc?.type || !desc?.sdp) throw new Error('Geçersiz SDP');
        return { type: desc.type, sdp: desc.sdp };
    }

    function resetMediaUi() {
        if (startMediaBtn) startMediaBtn.classList.remove('hidden');
        if (toggleMicBtn) toggleMicBtn.classList.add('hidden');
        if (toggleCamBtn) toggleCamBtn.classList.add('hidden');
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
        if (!csrf) throw new Error('Güvenlik jetonu yok — sayfayı yenileyin (Ctrl+F5).');

        const res = await fetch(signalSendUrl, {
            method: 'POST',
            headers: {
                ...authHeaders(),
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
            cache: 'no-store',
            body: JSON.stringify({ type, payload }),
        });

        const data = await res.json().catch(() => ({}));
        if (res.status === 419) throw new Error('Oturum süresi doldu — sayfayı yenileyin.');
        if (!res.ok) throw new Error(data.message || `Sinyal gönderilemedi (${res.status})`);
        return data;
    }

    async function flushIceQueue() {
        while (iceQueue.length && pc?.remoteDescription) {
            const cand = iceQueue.shift();
            try {
                await pc.addIceCandidate(new RTCIceCandidate(cand));
            } catch (_) { /* devam */ }
        }
    }

    function attachRemoteStream(stream) {
        if (!remoteVideo || !stream) return;
        remoteVideo.srcObject = stream;
        remoteVideo.muted = false;
        remoteVideo.play?.().catch(() => {});
    }

    function createPeerConnection() {
        if (pc) {
            pc.onicecandidate = null;
            pc.ontrack = null;
            pc.onconnectionstatechange = null;
            pc.oniceconnectionstatechange = null;
            pc.close();
            pc = null;
        }
        iceQueue.length = 0;

        pc = new RTCPeerConnection({ iceServers: ICE_SERVERS, iceCandidatePoolSize: 8 });

        pc.onicecandidate = (ev) => {
            if (ev.candidate) {
                postSignal('ice', ev.candidate.toJSON()).catch(() => {});
            }
        };

        pc.ontrack = (ev) => {
            const stream = ev.streams?.[0] || new MediaStream([ev.track]);
            attachRemoteStream(stream);
            setWebrtcStatus('Karşı taraf görüntüsü alındı');
        };

        pc.onconnectionstatechange = () => {
            if (!pc) return;
            const st = pc.connectionState;
            if (st === 'connected') {
                setWebrtcStatus('Bağlandı — ses ve görüntü aktif');
                clearInterval(reconnectTimer);
                reconnectTimer = null;
                clearTimeout(guestWaitTimer);
                guestWaitTimer = null;
            } else if (st === 'failed') {
                setWebrtcStatus('Bağlantı başarısız — yeniden deneniyor…');
                scheduleReconnect();
            }
        };

        pc.oniceconnectionstatechange = () => {
            if (!pc) return;
            if (pc.iceConnectionState === 'failed') {
                setWebrtcStatus('Ağ bağlantısı başarısız — TURN ile yeniden deneniyor…');
                scheduleReconnect();
            }
        };

        if (localStream) {
            localStream.getTracks().forEach((t) => pc.addTrack(t, localStream));
        }
    }

    async function applyRemoteDescription(payload) {
        if (!payload?.type || !payload?.sdp) throw new Error('Boş SDP alındı');
        await pc.setRemoteDescription(new RTCSessionDescription(payload));
        await flushIceQueue();
    }

    async function handleSignal(signal) {
        if (!pc && signal.type !== 'offer') return false;

        if (signal.type === 'offer') {
            if (!localStream) throw new Error('not_ready');
            if (!pc) createPeerConnection();
            else if (pc.currentRemoteDescription) createPeerConnection();
            await applyRemoteDescription(signal.payload);
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await postSignal('answer', sdpPayload(pc.localDescription));
            setWebrtcStatus('Yanıt gönderildi — bağlanılıyor…');
            return true;
        }

        if (signal.type === 'answer') {
            if (!pc) throw new Error('no_pc');
            await applyRemoteDescription(signal.payload);
            setWebrtcStatus('Karşı taraf yanıt verdi — bağlanılıyor…');
            return true;
        }

        if (signal.type === 'ice') {
            if (!signal.payload?.candidate) return true;
            if (!pc?.remoteDescription) {
                iceQueue.push(signal.payload);
                return false;
            }
            await pc.addIceCandidate(new RTCIceCandidate(signal.payload));
            return true;
        }

        return true;
    }

    async function pollSignals() {
        const res = await fetch(signalPollUrl, {
            method: 'POST',
            headers: {
                ...authHeaders(),
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
            },
            credentials: 'same-origin',
            cache: 'no-store',
            body: JSON.stringify({ after: lastSignalId }),
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            setWebrtcStatus(data.error === 'unauthorized'
                ? 'Kimlik doğrulama hatası — sayfayı yenileyin'
                : `Sinyal alınamadı (${res.status})`);
            return;
        }

        for (const sig of data.signals || []) {
            try {
                const done = await handleSignal({ type: sig.type, payload: sig.payload });
                if (done) {
                    lastSignalId = Math.max(lastSignalId, sig.id);
                    signalsReceived += 1;
                }
            } catch (err) {
                if (err?.message === 'not_ready') continue;
                setWebrtcStatus(err?.message || 'Sinyal işlenemedi…');
            }
        }
    }

    function startSignalPolling() {
        if (signalPollTimer) return;
        pollSignals();
        signalPollTimer = setInterval(pollSignals, 400);
    }

    function stopSignalPolling() {
        clearInterval(signalPollTimer);
        signalPollTimer = null;
    }

    function teardownMedia() {
        stopSignalPolling();
        clearInterval(reconnectTimer);
        reconnectTimer = null;
        clearTimeout(guestWaitTimer);
        guestWaitTimer = null;
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
        signalsReceived = 0;
        iceQueue.length = 0;
        resetMediaUi();
        setWebrtcStatus('Görüntülü bağlantı sonlandı');
    }

    async function sendOffer() {
        if (!pc) return;
        const offer = await pc.createOffer({ iceRestart: !!pc.currentRemoteDescription });
        await pc.setLocalDescription(offer);
        await postSignal('offer', sdpPayload(pc.localDescription));
        setWebrtcStatus('Teklif gönderildi — karşı taraf bekleniyor…');
    }

    function scheduleReconnect() {
        if (reconnectTimer || role !== 'owner') return;
        reconnectTimer = setInterval(async () => {
            if (!pc || pc.connectionState === 'connected') return;
            try {
                lastSignalId = 0;
                createPeerConnection();
                await sendOffer();
            } catch (_) { /* devam */ }
        }, 6000);
    }

    function scheduleGuestResync() {
        if (guestWaitTimer || role !== 'guest') return;
        guestWaitTimer = setTimeout(() => {
            guestWaitTimer = null;
            if (pc?.connectionState === 'connected') return;
            lastSignalId = 0;
            iceQueue.length = 0;
            pollSignals();
            scheduleGuestResync();
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
                await localVideo.play?.().catch(() => {});
            }
            if (toggleMicBtn) toggleMicBtn.classList.remove('hidden');
            if (toggleCamBtn) toggleCamBtn.classList.remove('hidden');

            createPeerConnection();
            startSignalPolling();

            if (role === 'owner') {
                await new Promise((r) => setTimeout(r, 800));
                await sendOffer();
                scheduleReconnect();
            } else {
                setWebrtcStatus('Oda sahibinden teklif bekleniyor…');
                scheduleGuestResync();
            }
        } catch (err) {
            mediaStarted = false;
            resetMediaUi();
            const denied = err?.name === 'NotAllowedError' || err?.name === 'PermissionDeniedError';
            setWebrtcStatus(denied
                ? 'Kamera/mikrofon izni reddedildi.'
                : (err?.message || 'Bağlantı kurulamadı — Ctrl+F5 ile yenileyin.'));
        }
    }

    async function pollStatus() {
        try {
            const res = await fetch(statusUrl, {
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
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
        } catch (_) { /* sessiz */ }
    }

    function sendLeaveBeacon() {
        if (role !== 'owner' || !leaveUrl || !csrf) return;
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
    if (participantCount >= 2) setTimeout(startVideoChat, 400);
})();

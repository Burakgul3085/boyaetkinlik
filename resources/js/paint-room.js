/**
 * Görüntülü boyama odası — WebRTC P2P (ses + görüntü)
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
    let localMediaReady = false;
    let callActive = false;
    let signalPollTimer = null;
    let reconnectTimer = null;
    let guestWaitTimer = null;
    let micEnabled = true;
    let camEnabled = true;
    const iceQueue = [];

    function authHeaders(json = false) {
        const headers = {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        };
        if (json) headers['Content-Type'] = 'application/json';
        if (csrf) headers['X-CSRF-TOKEN'] = csrf;
        if (guestToken) headers['X-Paint-Room-Guest-Token'] = guestToken;
        return headers;
    }

    function setWebrtcStatus(msg) {
        if (webrtcStatus) webrtcStatus.textContent = msg;
    }

    function sdpPayload(desc) {
        if (!desc?.type || !desc?.sdp) throw new Error('Geçersiz bağlantı verisi');
        return { type: desc.type, sdp: desc.sdp };
    }

    function showMediaControls() {
        if (toggleMicBtn) toggleMicBtn.classList.remove('hidden');
        if (toggleCamBtn) toggleCamBtn.classList.remove('hidden');
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
        if (!csrf) throw new Error('Sayfayı yenileyin (Ctrl+F5).');

        const res = await fetch(signalSendUrl, {
            method: 'POST',
            headers: authHeaders(true),
            credentials: 'same-origin',
            cache: 'no-store',
            body: JSON.stringify({ type, payload }),
        });

        const data = await res.json().catch(() => ({}));
        if (res.status === 419) throw new Error('Oturum süresi doldu — sayfayı yenileyin.');
        if (!res.ok) throw new Error(data.message || `Sinyal hatası (${res.status})`);
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
            setWebrtcStatus('Karşı tarafın görüntüsü geldi');
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
                setWebrtcStatus('Bağlantı kurulamadı — yeniden deneniyor…');
                scheduleReconnect();
            }
        };

        if (localStream) {
            localStream.getTracks().forEach((t) => pc.addTrack(t, localStream));
        }
    }

    async function applyRemoteDescription(payload) {
        if (!payload?.type || !payload?.sdp) throw new Error('Boş bağlantı verisi');
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
            headers: authHeaders(true),
            credentials: 'same-origin',
            cache: 'no-store',
            body: JSON.stringify({ after: lastSignalId }),
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            setWebrtcStatus(res.status === 403
                ? 'Kimlik doğrulama hatası — sayfayı yenileyin'
                : `Sinyal alınamadı (${res.status})`);
            return;
        }

        for (const sig of data.signals || []) {
            try {
                const done = await handleSignal({ type: sig.type, payload: sig.payload });
                if (done) lastSignalId = Math.max(lastSignalId, sig.id);
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

    function teardownCall() {
        stopSignalPolling();
        clearInterval(reconnectTimer);
        reconnectTimer = null;
        clearTimeout(guestWaitTimer);
        guestWaitTimer = null;
        if (pc) {
            pc.close();
            pc = null;
        }
        if (remoteVideo) remoteVideo.srcObject = null;
        callActive = false;
        lastSignalId = 0;
        iceQueue.length = 0;
    }

    function teardownAll() {
        teardownCall();
        if (localStream) {
            localStream.getTracks().forEach((t) => t.stop());
            localStream = null;
        }
        if (localVideo) localVideo.srcObject = null;
        localMediaReady = false;
        if (toggleMicBtn) toggleMicBtn.classList.add('hidden');
        if (toggleCamBtn) toggleCamBtn.classList.add('hidden');
        setWebrtcStatus('Görüntülü bağlantı sonlandı');
    }

    async function sendOffer() {
        if (!pc) return;
        const offer = await pc.createOffer();
        await pc.setLocalDescription(offer);
        await postSignal('offer', sdpPayload(pc.localDescription));
        setWebrtcStatus('Teklif gönderildi — karşı taraf bekleniyor…');
    }

    function scheduleReconnect() {
        if (reconnectTimer || role !== 'owner' || !callActive) return;
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
        if (guestWaitTimer || role !== 'guest' || !callActive) return;
        guestWaitTimer = setTimeout(() => {
            guestWaitTimer = null;
            if (pc?.connectionState === 'connected') return;
            lastSignalId = 0;
            iceQueue.length = 0;
            pollSignals();
            scheduleGuestResync();
        }, 8000);
    }

    async function initLocalMedia() {
        if (localMediaReady) return;
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
            localMediaReady = true;
            showMediaControls();

            if (participantCount >= 2) {
                setWebrtcStatus('Kamera hazır — bağlantı kuruluyor…');
                await startCall();
            } else if (role === 'owner') {
                setWebrtcStatus('Kamera hazır — misafir bekleniyor…');
            } else {
                setWebrtcStatus('Kamera hazır — oda sahibine bağlanılıyor…');
            }
        } catch (err) {
            const denied = err?.name === 'NotAllowedError' || err?.name === 'PermissionDeniedError';
            setWebrtcStatus(denied
                ? 'Kamera/mikrofon izni reddedildi — tarayıcı ayarlarından izin verin.'
                : (err?.message || 'Kamera açılamadı.'));
        }
    }

    async function startCall() {
        if (callActive || !localMediaReady || participantCount < 2) return;
        callActive = true;

        createPeerConnection();
        startSignalPolling();

        if (role === 'owner') {
            await new Promise((r) => setTimeout(r, 600));
            try {
                await sendOffer();
                scheduleReconnect();
            } catch (err) {
                setWebrtcStatus(err?.message || 'Bağlantı başlatılamadı');
                callActive = false;
            }
        } else {
            setWebrtcStatus('Oda sahibinden bağlantı bekleniyor…');
            scheduleGuestResync();
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
                teardownAll();
                window.location.href = indexUrl;
                return;
            }

            const prev = participantCount;
            participantCount = data.participants;
            if (countEl) countEl.textContent = String(participantCount);
            if (statusText) statusText.textContent = data.message;

            if (participantCount < 2 && prev >= 2) {
                teardownCall();
                if (localMediaReady && role === 'owner') {
                    setWebrtcStatus('Kamera hazır — misafir bekleniyor…');
                }
            }

            if (participantCount >= 2 && localMediaReady && !callActive) {
                await startCall();
            }
        } catch (_) { /* sessiz */ }
    }

    function sendLeaveBeacon() {
        if (role !== 'owner' || !leaveUrl || !csrf) return;
        const body = new URLSearchParams({ _token: csrf });
        if (navigator.sendBeacon) navigator.sendBeacon(leaveUrl, body);
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
        window.addEventListener('pagehide', () => { teardownAll(); sendLeaveBeacon(); });
    } else {
        window.addEventListener('pagehide', teardownAll);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
    pollStatus();
    setInterval(pollStatus, 2000);

    // Sayfa açılır açılmaz kamera/mikrofon izni iste
    initLocalMedia();
})();

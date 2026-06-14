/**
 * Görüntülü boyama odası — WebRTC P2P v4
 */
(function () {
    const root = document.getElementById('paint-room-lobby');
    if (!root) return;

    const statusUrl = root.dataset.statusUrl;
    const signalPollUrl = root.dataset.signalPollUrl;
    const signalSendUrl = root.dataset.signalSendUrl;
    const healthUrl = root.dataset.healthUrl;
    const leaveUrl = root.dataset.leaveUrl;
    const indexUrl = root.dataset.indexUrl;
    const role = root.dataset.role;
    const guestToken = root.dataset.guestToken || '';
    const expiresAt = new Date(root.dataset.expiresAt);
    const csrf = root.dataset.csrf
        || document.querySelector('meta[name="csrf-token"]')?.content
        || '';

    let ICE_SERVERS = [
        { urls: 'stun:stun.l.google.com:19302' },
        { urls: 'stun:stun1.l.google.com:19302' },
    ];
    try {
        const parsed = JSON.parse(root.dataset.iceServers || '[]');
        if (Array.isArray(parsed) && parsed.length) ICE_SERVERS = parsed;
    } catch (_) { /* varsayılan */ }

    const countEl = document.getElementById('paint-room-count');
    const statusText = document.getElementById('paint-room-status-text');
    const timerEl = document.getElementById('paint-room-timer');
    const webrtcStatus = document.getElementById('paint-room-webrtc-status');
    const debugEl = document.getElementById('paint-room-debug');
    const localVideo = document.getElementById('paint-room-local');
    const remoteVideo = document.getElementById('paint-room-remote');
    const remoteAudio = document.getElementById('paint-room-remote-audio');
    const unlockAudioBtn = document.getElementById('paint-room-unlock-audio');
    const toggleMicBtn = document.getElementById('paint-room-toggle-mic');
    const toggleCamBtn = document.getElementById('paint-room-toggle-cam');

    let participantCount = parseInt(countEl?.textContent || '1', 10);
    let pc = null;
    let localStream = null;
    let remoteStream = null;
    let remoteAudioStream = null;
    let lastSignalId = 0;
    let localMediaReady = false;
    let callActive = false;
    let signalPollTimer = null;
    let reconnectTimer = null;
    let micEnabled = true;
    let camEnabled = true;
    const iceQueue = [];
    const processedSignals = new Set();

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

    function setDebug(extra) {
        if (!debugEl) return;
        debugEl.classList.remove('hidden');
        const pcState = pc?.connectionState || '-';
        const iceState = pc?.iceConnectionState || '-';
        const base = `rol=${role} | kişi=${participantCount} | pc=${pcState} | ice=${iceState} | sinyal=${lastSignalId}`;
        debugEl.textContent = extra ? `${base} | ${extra}` : base;
    }

    function sdpPayload(desc) {
        if (!desc?.type || !desc?.sdp) throw new Error('Geçersiz SDP');
        return { type: desc.type, sdp: desc.sdp };
    }

    function showMediaControls() {
        toggleMicBtn?.classList.remove('hidden');
        toggleCamBtn?.classList.remove('hidden');
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
        if (!csrf) throw new Error('Sayfayı Ctrl+F5 ile yenileyin.');

        const body = new FormData();
        body.append('_token', csrf);
        body.append('type', type);
        body.append('payload', JSON.stringify(payload));

        const res = await fetch(signalSendUrl, {
            method: 'POST',
            headers: authHeaders(),
            credentials: 'same-origin',
            cache: 'no-store',
            body,
        });

        const data = await res.json().catch(() => ({}));
        if (res.status === 419) throw new Error('Oturum süresi doldu — sayfayı yenileyin.');
        if (!res.ok) throw new Error(data.message || `Gönderim hatası ${res.status}`);
        setDebug(`gönderildi: ${type} #${data.id || '?'}`);
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

    function showAudioUnlock() {
        unlockAudioBtn?.classList.remove('hidden');
        setWebrtcStatus('Ses için "Sesi aç" butonuna tıklayın');
    }

    function hideAudioUnlock() {
        unlockAudioBtn?.classList.add('hidden');
    }

    async function playRemoteAudio() {
        if (!remoteAudio) return false;
        remoteAudio.muted = false;
        remoteAudio.volume = 1;
        try {
            await remoteAudio.play();
            hideAudioUnlock();
            return true;
        } catch (_) {
            showAudioUnlock();
            return false;
        }
    }

    function attachRemoteVideoTrack(track) {
        if (!remoteVideo || !track) return;
        if (!remoteStream) remoteStream = new MediaStream();
        remoteStream.getVideoTracks().forEach((t) => remoteStream.removeTrack(t));
        remoteStream.addTrack(track);
        track.enabled = true;
        remoteVideo.srcObject = remoteStream;
        remoteVideo.muted = true;
        remoteVideo.playsInline = true;
        remoteVideo.play?.().catch(() => {});
    }

    function attachRemoteAudioTrack(track) {
        if (!remoteAudio || !track) return;
        if (!remoteAudioStream) remoteAudioStream = new MediaStream();
        remoteAudioStream.getAudioTracks().forEach((t) => remoteAudioStream.removeTrack(t));
        remoteAudioStream.addTrack(track);
        track.enabled = true;
        remoteAudio.srcObject = remoteAudioStream;
        playRemoteAudio();
        setDebug(`ses track: ${track.label || 'audio'}`);
    }

    function attachRemoteStream(stream) {
        if (!stream) return;
        stream.getVideoTracks().forEach((t) => attachRemoteVideoTrack(t));
        stream.getAudioTracks().forEach((t) => attachRemoteAudioTrack(t));
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
        remoteStream = null;
        remoteAudioStream = null;
        if (remoteAudio) remoteAudio.srcObject = null;
        hideAudioUnlock();

        pc = new RTCPeerConnection({
            iceServers: ICE_SERVERS,
            iceCandidatePoolSize: 10,
            bundlePolicy: 'max-bundle',
        });

        pc.onicecandidate = (ev) => {
            if (ev.candidate) {
                postSignal('ice', ev.candidate.toJSON()).catch((e) => setDebug(`ice hata: ${e.message}`));
            }
        };

        pc.ontrack = (ev) => {
            const track = ev.track;
            if (!track) return;
            track.enabled = true;

            if (track.kind === 'audio') {
                attachRemoteAudioTrack(track);
            } else if (track.kind === 'video') {
                attachRemoteVideoTrack(track);
            }

            setWebrtcStatus('Karşı taraf bağlandı');
            setDebug(`track: ${track.kind}`);
        };

        pc.onconnectionstatechange = () => {
            if (!pc) return;
            setDebug();
            if (pc.connectionState === 'connected') {
                setWebrtcStatus('Bağlandı — ses ve görüntü aktif');
                playRemoteAudio();
                clearInterval(reconnectTimer);
                reconnectTimer = null;
            } else if (pc.connectionState === 'failed') {
                setWebrtcStatus('Bağlantı başarısız — yeniden deneniyor…');
                scheduleReconnect();
            }
        };

        pc.oniceconnectionstatechange = () => {
            if (!pc) return;
            setDebug(`ice durumu: ${pc.iceConnectionState}`);
            if (pc.iceConnectionState === 'failed') scheduleReconnect();
        };

        if (localStream) {
            localStream.getTracks().forEach((t) => pc.addTrack(t, localStream));
        }
    }

    async function applyRemoteDescription(payload) {
        if (!payload?.type || !payload?.sdp) throw new Error('Boş SDP');
        if (pc.signalingState === 'stable' && pc.currentRemoteDescription) {
            createPeerConnection();
        }
        await pc.setRemoteDescription(new RTCSessionDescription(payload));
        await flushIceQueue();
    }

    async function ensureCallReady() {
        if (!localMediaReady || participantCount < 2) return false;
        if (!callActive) {
            callActive = true;
            createPeerConnection();
            startSignalPolling();
        } else if (!pc) {
            createPeerConnection();
        }
        return true;
    }

    async function handleSignal(signal, signalId) {
        if (processedSignals.has(signalId)) return true;
        if (!pc && signal.type !== 'offer') return false;

        if (signal.type === 'offer') {
            if (!localStream) throw new Error('not_ready');
            await ensureCallReady();
            if (!pc) createPeerConnection();
            await applyRemoteDescription(signal.payload);
            const answer = await pc.createAnswer();
            await pc.setLocalDescription(answer);
            await postSignal('answer', sdpPayload(pc.localDescription));
            processedSignals.add(signalId);
            setWebrtcStatus('Yanıt gönderildi — bağlanılıyor…');
            return true;
        }

        if (signal.type === 'answer') {
            if (!pc) await ensureCallReady();
            if (!pc) throw new Error('no_pc');
            await applyRemoteDescription(signal.payload);
            processedSignals.add(signalId);
            setWebrtcStatus('Karşı taraf yanıt verdi — bağlanılıyor…');
            return true;
        }

        if (signal.type === 'ice') {
            if (!signal.payload?.candidate) {
                processedSignals.add(signalId);
                return true;
            }
            if (!pc?.remoteDescription) {
                iceQueue.push(signal.payload);
                return false;
            }
            await pc.addIceCandidate(new RTCIceCandidate(signal.payload));
            processedSignals.add(signalId);
            return true;
        }

        return true;
    }

    async function pollSignals() {
        if (!csrf) return;

        const body = new FormData();
        body.append('_token', csrf);
        body.append('after', String(lastSignalId));

        const res = await fetch(signalPollUrl, {
            method: 'POST',
            headers: authHeaders(),
            credentials: 'same-origin',
            cache: 'no-store',
            body,
        });

        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = res.status === 403
                ? 'Kimlik hatası — sayfayı yenileyin'
                : `Sinyal alınamadı (${res.status})`;
            setWebrtcStatus(msg);
            setDebug(msg);
            return;
        }

        if ((data.signals || []).length) {
            setDebug(`gelen: ${data.signals.length} sinyal`);
        }

        for (const sig of data.signals || []) {
            try {
                const done = await handleSignal(
                    { type: sig.type, payload: sig.payload },
                    sig.id,
                );
                if (done) lastSignalId = Math.max(lastSignalId, sig.id);
            } catch (err) {
                if (err?.message === 'not_ready') continue;
                setWebrtcStatus(err?.message || 'Sinyal hatası');
                setDebug(err?.message || 'sinyal hatası');
            }
        }
    }

    function startSignalPolling() {
        if (signalPollTimer) return;
        pollSignals();
        signalPollTimer = setInterval(pollSignals, 350);
    }

    function stopSignalPolling() {
        clearInterval(signalPollTimer);
        signalPollTimer = null;
    }

    function teardownCall() {
        stopSignalPolling();
        clearInterval(reconnectTimer);
        reconnectTimer = null;
        if (pc) {
            pc.close();
            pc = null;
        }
        if (remoteVideo) remoteVideo.srcObject = null;
        if (remoteAudio) remoteAudio.srcObject = null;
        remoteStream = null;
        remoteAudioStream = null;
        hideAudioUnlock();
        callActive = false;
        lastSignalId = 0;
        iceQueue.length = 0;
        processedSignals.clear();
    }

    function teardownAll() {
        teardownCall();
        if (localStream) {
            localStream.getTracks().forEach((t) => t.stop());
            localStream = null;
        }
        if (localVideo) localVideo.srcObject = null;
        localMediaReady = false;
        toggleMicBtn?.classList.add('hidden');
        toggleCamBtn?.classList.add('hidden');
        setWebrtcStatus('Bağlantı sonlandı');
    }

    async function sendOffer() {
        if (!pc) return;
        const offer = await pc.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: true });
        await pc.setLocalDescription(offer);
        await postSignal('offer', sdpPayload(pc.localDescription));
        setWebrtcStatus('Teklif gönderildi — karşı taraf bekleniyor…');
    }

    function scheduleReconnect() {
        if (reconnectTimer || role !== 'owner' || !callActive) return;
        reconnectTimer = setInterval(async () => {
            if (pc?.connectionState === 'connected') return;
            try {
                lastSignalId = 0;
                processedSignals.clear();
                createPeerConnection();
                await sendOffer();
            } catch (e) {
                setDebug(`yeniden: ${e.message}`);
            }
        }, 5000);
    }

    async function startCall() {
        if (callActive || !localMediaReady || participantCount < 2) return;
        callActive = true;
        createPeerConnection();
        startSignalPolling();

        if (role === 'owner') {
            await new Promise((r) => setTimeout(r, 500));
            try {
                await sendOffer();
                scheduleReconnect();
            } catch (err) {
                setWebrtcStatus(err?.message || 'Bağlantı başlatılamadı');
                callActive = false;
            }
        } else {
            setWebrtcStatus('Oda sahibinden teklif bekleniyor…');
        }
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
            hideAudioUnlock();
            setDebug(`kamera+hazır, mic=${localStream.getAudioTracks().length}`);

            if (participantCount >= 2) {
                await startCall();
            } else {
                setWebrtcStatus('Kamera hazır — misafir bekleniyor…');
            }
        } catch (err) {
            const denied = err?.name === 'NotAllowedError' || err?.name === 'PermissionDeniedError';
            setWebrtcStatus(denied
                ? 'Kamera/mikrofon izni reddedildi.'
                : (err?.message || 'Kamera açılamadı.'));
            setDebug(err?.message || 'medya hatası');
        }
    }

    async function checkHealth() {
        if (!healthUrl) return;
        try {
            const res = await fetch(healthUrl, {
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
            });
            const data = await res.json().catch(() => ({}));
            if (!data.table) {
                setWebrtcStatus('Sunucu tablosu eksik — migrate çalıştırın');
                setDebug('paint_room_signals tablosu YOK');
                return;
            }
            if (!data.role) {
                setWebrtcStatus('Oturum hatası — sayfayı yenileyin');
                setDebug('rol tanınmadı');
                return;
            }
            setDebug(`saglik OK, sinyal=${data.signalCount}`);
        } catch (e) {
            setDebug(`saglik hata: ${e.message}`);
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
                if (localMediaReady) setWebrtcStatus('Kamera hazır — misafir bekleniyor…');
            }

            if (participantCount >= 2 && localMediaReady && !callActive) {
                await startCall();
            }
        } catch (_) { /* sessiz */ }
    }

    function sendLeaveBeacon() {
        if (role !== 'owner' || !leaveUrl || !csrf) return;
        navigator.sendBeacon?.(leaveUrl, new URLSearchParams({ _token: csrf }));
    }

    unlockAudioBtn?.addEventListener('click', async () => {
        const ok = await playRemoteAudio();
        if (ok) setWebrtcStatus('Ses açıldı');
    });

    toggleMicBtn?.addEventListener('click', () => {
        if (!localStream) return;
        micEnabled = !micEnabled;
        localStream.getAudioTracks().forEach((t) => { t.enabled = micEnabled; });
        toggleMicBtn.textContent = micEnabled ? 'Mikrofonu kapat' : 'Mikrofonu aç';
    });

    toggleCamBtn?.addEventListener('click', () => {
        if (!localStream) return;
        camEnabled = !camEnabled;
        localStream.getVideoTracks().forEach((t) => { t.enabled = camEnabled; });
        toggleCamBtn.textContent = camEnabled ? 'Kamerayı kapat' : 'Kamerayı aç';
    });

    if (role === 'owner') {
        window.addEventListener('pagehide', () => { teardownAll(); sendLeaveBeacon(); });
    } else {
        window.addEventListener('pagehide', teardownAll);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
    checkHealth();
    pollStatus();
    setInterval(pollStatus, 2000);
    initLocalMedia();
})();

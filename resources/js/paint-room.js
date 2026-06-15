/**
 * Görüntülü boyama odası — WebRTC P2P + ortak tuval
 */
import { initPaintRoomCanvas } from './paint-room-canvas.js';

(function () {
    const root = document.getElementById('paint-room-lobby');
    if (!root) return;

    const statusUrl = root.dataset.statusUrl;
    const signalPollUrl = root.dataset.signalPollUrl;
    const signalSendUrl = root.dataset.signalSendUrl;
    const healthUrl = root.dataset.healthUrl;
    const canvasLoadUrl = root.dataset.canvasLoadUrl;
    const canvasSaveUrl = root.dataset.canvasSaveUrl;
    const lineArtUrl = root.dataset.lineArtUrl || null;
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
    let paintDc = null;
    let canvasApi = null;
    let canvasSaveTimer = null;
    let micEnabled = true;
    let camEnabled = true;
    let reconnectAttempts = 0;
    const iceQueue = [];
    const processedSignals = new Set();

    function isConnected() {
        return pc?.connectionState === 'connected'
            || pc?.iceConnectionState === 'connected'
            || pc?.iceConnectionState === 'completed';
    }

    function canAcceptAnswer() {
        return pc?.signalingState === 'have-local-offer';
    }

    function canAcceptOffer() {
        if (!pc) return true;
        if (isConnected()) return false;
        return pc.signalingState === 'stable';
    }

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

    function setDebug(_extra) {
        /* Teknik loglar yalnızca geliştirme için; kullanıcı arayüzünde gösterilmez */
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
        const existing = remoteStream.getVideoTracks().find((t) => t.id === track.id);
        if (!existing) {
            remoteStream.getVideoTracks().forEach((t) => remoteStream.removeTrack(t));
            remoteStream.addTrack(track);
        }
        track.enabled = true;
        remoteVideo.srcObject = remoteStream;
        remoteVideo.muted = true;
        remoteVideo.playsInline = true;
        remoteVideo.play?.().catch(() => {});
    }

    function attachRemoteAudioTrack(track) {
        if (!remoteAudio || !track) return;
        if (!remoteAudioStream) remoteAudioStream = new MediaStream();
        const existing = remoteAudioStream.getAudioTracks().find((t) => t.id === track.id);
        if (!existing) {
            remoteAudioStream.getAudioTracks().forEach((t) => remoteAudioStream.removeTrack(t));
            remoteAudioStream.addTrack(track);
        }
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

    let canvasSyncTimer = null;

    function startCanvasSyncFallback() {
        if (canvasSyncTimer) return;
        canvasSyncTimer = setInterval(() => {
            if (participantCount >= 2 && paintDc?.readyState !== 'open' && canvasApi?.isReady()) {
                loadCanvasFromServer();
            }
        }, 2500);
    }

    function sendPaint(msg) {
        if (paintDc?.readyState === 'open') {
            paintDc.send(JSON.stringify(msg));
        }
    }

    function requestPaintSync() {
        if (paintDc?.readyState === 'open' && role === 'guest') {
            paintDc.send(JSON.stringify({ t: 'sync-req' }));
        }
    }

    function wirePaintDc(dc) {
        dc.onopen = () => {
            setDebug('boyama kanalı açık');
            requestPaintSync();
            if (canvasApi?.isReady()) {
                loadCanvasFromServer();
            }
        };
        dc.onmessage = (ev) => {
            let msg;
            try { msg = JSON.parse(ev.data); } catch { return; }
            if (!canvasApi) return;
            if (msg.t === 'stroke') canvasApi.drawRemoteStroke(msg);
            if (msg.t === 'fill') canvasApi.applyRemoteFill(msg);
            if (msg.t === 'clear') canvasApi.clear(false);
            if (msg.t === 'sync-req' && role === 'owner') {
                const snap = canvasApi.getSnapshot();
                if (snap) dc.send(JSON.stringify({ t: 'sync', data: snap }));
            }
            if (msg.t === 'sync' && canvasApi.isReady()) {
                canvasApi.applySnapshot(msg.data);
            }
        };
    }

    function setupPaintChannel() {
        paintDc = null;
        if (role === 'owner') {
            paintDc = pc.createDataChannel('paint', { ordered: true });
            wirePaintDc(paintDc);
        } else {
            pc.ondatachannel = (ev) => {
                if (ev.channel.label !== 'paint') return;
                paintDc = ev.channel;
                wirePaintDc(paintDc);
            };
        }
    }

    function scheduleCanvasSave() {
        if (canvasSaveTimer) return;
        canvasSaveTimer = setTimeout(async () => {
            canvasSaveTimer = null;
            await saveCanvasToServer();
        }, 4000);
    }

    async function saveCanvasToServer() {
        if (!canvasSaveUrl || !canvasApi || !csrf) return;
        const image = canvasApi.getSnapshot();
        if (!image) return;
        const body = new FormData();
        body.append('_token', csrf);
        body.append('image', image);
        try {
            await fetch(canvasSaveUrl, {
                method: 'POST',
                headers: authHeaders(),
                credentials: 'same-origin',
                body,
            });
        } catch (_) { /* sessiz */ }
    }

    async function loadCanvasFromServer() {
        if (!canvasLoadUrl || !canvasApi?.isReady()) return;
        try {
            const res = await fetch(canvasLoadUrl, {
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
            });
            const data = await res.json().catch(() => ({}));
            if (data.image) canvasApi.applySnapshot(data.image);
        } catch (_) { /* sessiz */ }
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

        setupPaintChannel();

        pc.onicecandidate = (ev) => {
            if (ev.candidate) {
                postSignal('ice', ev.candidate.toJSON()).catch((e) => setDebug(`ice hata: ${e.message}`));
            }
        };

        pc.ontrack = (ev) => {
            const stream = ev.streams?.[0];
            if (stream) {
                attachRemoteStream(stream);
            } else if (ev.track) {
                if (ev.track.kind === 'audio') {
                    attachRemoteAudioTrack(ev.track);
                } else if (ev.track.kind === 'video') {
                    attachRemoteVideoTrack(ev.track);
                }
            }

            setWebrtcStatus('Karşı taraf bağlandı');
            setDebug(`track: ${ev.track?.kind || 'stream'}`);
        };

        pc.onconnectionstatechange = () => {
            if (!pc) return;
            setDebug();
            if (pc.connectionState === 'connected') {
                reconnectAttempts = 0;
                setWebrtcStatus('Bağlandı — ses ve görüntü aktif');
                playRemoteAudio();
                clearInterval(reconnectTimer);
                reconnectTimer = null;
                requestPaintSync();
            } else if (pc.connectionState === 'failed' || pc.connectionState === 'disconnected') {
                setWebrtcStatus('Bağlantı koptu — yeniden deneniyor…');
                scheduleReconnect();
            }
        };

        pc.oniceconnectionstatechange = () => {
            if (!pc) return;
            setDebug(`ice: ${pc.iceConnectionState}`);
            if (pc.iceConnectionState === 'connected' || pc.iceConnectionState === 'completed') {
                playRemoteAudio();
                requestPaintSync();
            } else if (pc.iceConnectionState === 'failed') {
                scheduleReconnect();
            }
        };

        if (localStream) {
            localStream.getTracks().forEach((t) => pc.addTrack(t, localStream));
        }
    }

    async function applyRemoteDescription(payload) {
        if (!payload?.type || !payload?.sdp) throw new Error('Boş SDP');
        const desc = new RTCSessionDescription(payload);

        if (desc.type === 'answer') {
            if (!canAcceptAnswer()) {
                setDebug(`answer atlandı: ${pc?.signalingState}`);
                return;
            }
        }

        if (desc.type === 'offer') {
            if (!canAcceptOffer()) {
                setDebug(`offer atlandı: ${pc?.signalingState}/${pc?.connectionState}`);
                return;
            }
            if (pc.signalingState === 'have-local-offer') {
                createPeerConnection();
            }
        }

        await pc.setRemoteDescription(desc);
        await flushIceQueue();
    }

    async function handleSignal(signal, signalId) {
        if (processedSignals.has(signalId)) return true;

        if (signal.type === 'offer') {
            if (role !== 'guest') {
                processedSignals.add(signalId);
                return true;
            }
            if (!localStream) throw new Error('not_ready');
            await ensureCallReady();
            if (!canAcceptOffer()) {
                if (isConnected()) {
                    processedSignals.add(signalId);
                    return true;
                }
                createPeerConnection();
            }
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
            if (role !== 'owner') {
                processedSignals.add(signalId);
                return true;
            }
            if (!pc) await ensureCallReady();
            if (!pc) throw new Error('no_pc');
            if (!canAcceptAnswer()) {
                processedSignals.add(signalId);
                setDebug(`eski answer atlandı: ${pc.signalingState}`);
                return true;
            }
            await applyRemoteDescription(signal.payload);
            processedSignals.add(signalId);
            setWebrtcStatus('Karşı taraf yanıt verdi — bağlanılıyor…');
            return true;
        }

        if (signal.type === 'ice') {
            if (!pc) return false;
            if (!signal.payload?.candidate) {
                processedSignals.add(signalId);
                return true;
            }
            if (!pc.remoteDescription) {
                iceQueue.push(signal.payload);
                return false;
            }
            try {
                await pc.addIceCandidate(new RTCIceCandidate(signal.payload));
            } catch (_) { /* eski aday */ }
            processedSignals.add(signalId);
            return true;
        }

        processedSignals.add(signalId);
        return true;
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
        clearTimeout(reconnectTimer);
        reconnectTimer = null;
        if (pc) {
            pc.close();
            pc = null;
        }
        paintDc = null;
        if (remoteVideo) remoteVideo.srcObject = null;
        if (remoteAudio) remoteAudio.srcObject = null;
        remoteStream = null;
        remoteAudioStream = null;
        hideAudioUnlock();
        callActive = false;
        lastSignalId = 0;
        iceQueue.length = 0;
        processedSignals.clear();
        reconnectAttempts = 0;
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
        if (!pc || role !== 'owner') return;
        if (pc.localDescription?.type === 'offer') return;
        if (pc.signalingState !== 'stable') return;

        const offer = await pc.createOffer({
            offerToReceiveAudio: true,
            offerToReceiveVideo: true,
            iceRestart: reconnectAttempts > 0,
        });
        await pc.setLocalDescription(offer);
        await postSignal('offer', sdpPayload(pc.localDescription));
        setWebrtcStatus('Teklif gönderildi — karşı taraf bekleniyor…');
    }

    function scheduleReconnect() {
        if (reconnectTimer || role !== 'owner' || !callActive || isConnected()) return;
        if (reconnectAttempts >= 6) {
            setWebrtcStatus('Bağlantı kurulamadı — sayfayı yenileyin');
            return;
        }

        reconnectTimer = setTimeout(async () => {
            reconnectTimer = null;
            if (isConnected()) return;

            reconnectAttempts += 1;
            try {
                processedSignals.clear();
                createPeerConnection();
                await sendOffer();
            } catch (e) {
                setDebug(`yeniden: ${e.message}`);
                scheduleReconnect();
            }
        }, 3000 + reconnectAttempts * 1000);
    }

    async function startCall() {
        if (callActive || !localMediaReady || participantCount < 2) return;
        callActive = true;
        createPeerConnection();
        startSignalPolling();

        if (role === 'owner') {
            await new Promise((r) => setTimeout(r, 800));
            try {
                await sendOffer();
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

    function initPipDrag(pipEl) {
        const handle = document.getElementById('paint-room-pip-drag-handle');
        if (!pipEl || !handle) return;

        const STORAGE_KEY = 'paint-room-pip-pos';
        let dragging = false;
        let startX = 0;
        let startY = 0;
        let startLeft = 0;
        let startTop = 0;

        function clampPosition(left, top) {
            const rect = pipEl.getBoundingClientRect();
            const margin = 8;
            const maxLeft = window.innerWidth - rect.width - margin;
            const maxTop = window.innerHeight - rect.height - margin;
            return {
                left: Math.max(margin, Math.min(maxLeft, left)),
                top: Math.max(margin, Math.min(maxTop, top)),
            };
        }

        function applyPosition(left, top) {
            const clamped = clampPosition(left, top);
            pipEl.style.left = `${clamped.left}px`;
            pipEl.style.top = `${clamped.top}px`;
            pipEl.style.right = 'auto';
            pipEl.style.bottom = 'auto';
            pipEl.classList.add('paint-room-pip--dragged');
            try {
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(clamped));
            } catch (_) { /* depolama kapalı olabilir */ }
        }

        function restorePosition() {
            try {
                const saved = sessionStorage.getItem(STORAGE_KEY);
                if (!saved) return;
                const { left, top } = JSON.parse(saved);
                if (typeof left === 'number' && typeof top === 'number') {
                    applyPosition(left, top);
                }
            } catch (_) { /* geçersiz kayıt */ }
        }

        handle.addEventListener('pointerdown', (e) => {
            if (e.button !== 0 || e.target.closest('button')) return;
            dragging = true;
            handle.setPointerCapture(e.pointerId);
            const rect = pipEl.getBoundingClientRect();
            pipEl.style.right = 'auto';
            pipEl.style.bottom = 'auto';
            startLeft = rect.left;
            startTop = rect.top;
            startX = e.clientX;
            startY = e.clientY;
            pipEl.classList.add('paint-room-pip--grabbing');
            e.preventDefault();
        });

        handle.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            applyPosition(startLeft + e.clientX - startX, startTop + e.clientY - startY);
        });

        const endDrag = () => {
            if (!dragging) return;
            dragging = false;
            pipEl.classList.remove('paint-room-pip--grabbing');
        };

        handle.addEventListener('pointerup', endDrag);
        handle.addEventListener('pointercancel', endDrag);

        window.addEventListener('resize', () => {
            if (!pipEl.classList.contains('paint-room-pip--dragged')) return;
            const rect = pipEl.getBoundingClientRect();
            applyPosition(rect.left, rect.top);
        });

        restorePosition();
    }

    unlockAudioBtn?.addEventListener('click', async () => {
        const ok = await playRemoteAudio();
        if (ok) setWebrtcStatus('Ses açıldı');
    });

    toggleMicBtn?.addEventListener('click', () => {
        if (!localStream) return;
        micEnabled = !micEnabled;
        localStream.getAudioTracks().forEach((t) => { t.enabled = micEnabled; });
        toggleMicBtn.textContent = micEnabled ? '🎤' : '🔇';
        toggleMicBtn.title = micEnabled ? 'Mikrofonu kapat' : 'Mikrofonu aç';
    });

    toggleCamBtn?.addEventListener('click', () => {
        if (!localStream) return;
        camEnabled = !camEnabled;
        localStream.getVideoTracks().forEach((t) => { t.enabled = camEnabled; });
        toggleCamBtn.textContent = camEnabled ? '📷' : '🚫';
        toggleCamBtn.title = camEnabled ? 'Kamerayı kapat' : 'Kamerayı aç';
    });

    const pipEl = document.getElementById('paint-room-pip');
    const pipToggle = document.getElementById('paint-room-pip-toggle');
    const pipExpand = document.getElementById('paint-room-pip-expand');
    pipToggle?.addEventListener('click', () => {
        pipEl?.classList.toggle('paint-room-pip--collapsed');
        pipToggle.textContent = pipEl?.classList.contains('paint-room-pip--collapsed') ? '+' : '−';
    });
    pipExpand?.addEventListener('click', () => {
        pipEl?.classList.toggle('paint-room-pip--expanded');
    });

    initPipDrag(pipEl);

    const infoPanel = document.getElementById('paint-room-info-panel');
    const infoToggle = document.getElementById('paint-room-info-toggle');
    const infoClose = document.getElementById('paint-room-info-close');
    infoToggle?.addEventListener('click', () => infoPanel?.classList.remove('hidden'));
    infoClose?.addEventListener('click', () => infoPanel?.classList.add('hidden'));
    infoPanel?.addEventListener('click', (e) => {
        if (e.target === infoPanel) infoPanel.classList.add('hidden');
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

    canvasApi = initPaintRoomCanvas({
        lineArtUrl,
        onReady: () => {
            loadCanvasFromServer();
            canvasApi?.fitToView?.();
        },
        onStroke: (stroke) => {
            sendPaint({ t: 'stroke', ...stroke });
            scheduleCanvasSave();
        },
        onFill: (fill) => {
            sendPaint({ t: 'fill', ...fill });
            scheduleCanvasSave();
        },
        onClear: () => {
            sendPaint({ t: 'clear' });
            scheduleCanvasSave();
        },
    });
    startCanvasSyncFallback();
    initLocalMedia();
})();

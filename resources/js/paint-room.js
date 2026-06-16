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
    const changePageUrl = root.dataset.changePageUrl || '';
    let activeLineArtUrl = lineArtUrl;
    let currentColoringPageId = parseInt(root.dataset.coloringPageId || '0', 10) || null;
    const leaveUrl = root.dataset.leaveUrl;
    const indexUrl = root.dataset.indexUrl;
    const role = root.dataset.role;
    const chatSendUrl = root.dataset.chatSendUrl || '';
    const chatPollUrl = root.dataset.chatPollUrl || '';
    const chatHistoryUrl = root.dataset.chatHistoryUrl || '';
    const chatDisplayName = root.dataset.chatDisplayName || (role === 'owner' ? 'Oda sahibi' : 'Misafir');
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
    const chatPanel = document.getElementById('paint-room-chat');
    const chatMessagesEl = document.getElementById('paint-room-chat-messages');
    const chatEmptyEl = document.getElementById('paint-room-chat-empty');
    const chatForm = document.getElementById('paint-room-chat-form');
    const chatInput = document.getElementById('paint-room-chat-input');
    const chatSendBtn = document.getElementById('paint-room-chat-send');
    const chatToggleBtn = document.getElementById('paint-room-chat-toggle');
    const chatBadge = document.getElementById('paint-room-chat-badge');

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
    let lastChatId = 0;
    let chatPollTimer = null;
    let chatHistoryLoaded = false;
    let chatOpen = false;
    let chatUnread = 0;
    const seenChatIds = new Set();

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

    function lineArtUrlWithBust(baseUrl) {
        if (!baseUrl) return null;
        const sep = baseUrl.includes('?') ? '&' : '?';
        return `${baseUrl}${sep}_=${Date.now()}`;
    }

    function updatePageTitle(title) {
        const el = document.getElementById('paint-room-page-title');
        if (el && title) el.textContent = title;
    }

    function setActivePickerItem(pageId) {
        document.querySelectorAll('[data-paint-room-page-browser] .paint-room-page-picker__item').forEach((btn) => {
            btn.classList.toggle('paint-room-page-picker__item--active', btn.dataset.pageId === String(pageId));
            btn.disabled = btn.dataset.pageId === String(pageId);
        });
    }

    async function applyColoringPageChange(pageId, title, artUrl, options = {}) {
        const nextId = parseInt(String(pageId), 10);
        if (!nextId || nextId === currentColoringPageId) return;

        currentColoringPageId = nextId;
        root.dataset.coloringPageId = String(nextId);
        activeLineArtUrl = artUrl || lineArtUrl;
        updatePageTitle(title);
        setActivePickerItem(nextId);
        canvasApi?.reloadLineArt(lineArtUrlWithBust(activeLineArtUrl));
        if (options.notifyRemote) {
            sendPaint({ t: 'page-change', pageId: nextId, title });
        }
    }

    async function changeColoringPage(pageId, title) {
        if (role !== 'owner' || !changePageUrl || !csrf) return;
        const nextId = parseInt(String(pageId), 10);
        if (!nextId || nextId === currentColoringPageId) return;

        const ok = window.confirm('Boyama değişince tuval sıfırlanır. Devam edilsin mi?');
        if (!ok) return;

        const body = new FormData();
        body.append('_token', csrf);
        body.append('coloring_page_id', String(nextId));

        try {
            const res = await fetch(changePageUrl, {
                method: 'POST',
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
                body,
            });
            const data = await res.json().catch(() => ({}));
            if (res.status === 419) throw new Error('Oturum süresi doldu — sayfayı yenileyin.');
            if (!res.ok) throw new Error(data.message || 'Boyama değiştirilemedi');

            await applyColoringPageChange(
                data.coloringPageId,
                data.coloringPageTitle,
                data.lineArtUrl,
                { notifyRemote: true },
            );
        } catch (err) {
            setWebrtcStatus(err?.message || 'Boyama değiştirilemedi');
        }
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
            if (msg.t === 'page-change' && role === 'guest') {
                applyColoringPageChange(msg.pageId, msg.title, lineArtUrl);
            }
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
        stopChatPolling();
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

            const nextPageId = data.coloringPageId ? parseInt(String(data.coloringPageId), 10) : null;
            if (nextPageId && nextPageId !== currentColoringPageId) {
                await applyColoringPageChange(
                    nextPageId,
                    data.coloringPageTitle,
                    lineArtUrl,
                );
            }

            if (participantCount < 2 && prev >= 2) {
                teardownCall();
                stopChatPolling();
                if (localMediaReady) setWebrtcStatus('Kamera hazır — misafir bekleniyor…');
            }

            if (participantCount >= 2) {
                startChatPolling();
                if (!chatHistoryLoaded) loadChatHistory();
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
        const margin = 8;
        let dragging = false;
        let offsetX = 0;
        let offsetY = 0;
        let pipW = 0;
        let pipH = 0;
        let anchorLeft = 0;
        let anchorTop = 0;
        let lastLeft = 0;
        let lastTop = 0;

        function clampPosition(left, top) {
            const maxLeft = window.innerWidth - pipW - margin;
            const maxTop = window.innerHeight - pipH - margin;
            return {
                left: Math.max(margin, Math.min(maxLeft, left)),
                top: Math.max(margin, Math.min(maxTop, top)),
            };
        }

        function commitPosition(left, top, persist) {
            const clamped = clampPosition(left, top);
            lastLeft = clamped.left;
            lastTop = clamped.top;
            pipEl.style.left = `${clamped.left}px`;
            pipEl.style.top = `${clamped.top}px`;
            pipEl.style.right = 'auto';
            pipEl.style.bottom = 'auto';
            pipEl.style.transform = 'translate3d(0,0,0)';
            pipEl.classList.add('paint-room-pip--dragged');
            if (persist) {
                try {
                    sessionStorage.setItem(STORAGE_KEY, JSON.stringify(clamped));
                } catch (_) { /* depolama kapalı olabilir */ }
            }
            return clamped;
        }

        function moveByPointer(clientX, clientY) {
            const clamped = clampPosition(clientX - offsetX, clientY - offsetY);
            lastLeft = clamped.left;
            lastTop = clamped.top;
            pipEl.style.transform = `translate3d(${clamped.left - anchorLeft}px,${clamped.top - anchorTop}px,0)`;
        }

        function restorePosition() {
            try {
                const saved = sessionStorage.getItem(STORAGE_KEY);
                if (!saved) return;
                const { left, top } = JSON.parse(saved);
                if (typeof left === 'number' && typeof top === 'number') {
                    const rect = pipEl.getBoundingClientRect();
                    pipW = rect.width;
                    pipH = rect.height;
                    commitPosition(left, top, false);
                }
            } catch (_) { /* geçersiz kayıt */ }
        }

        handle.addEventListener('pointerdown', (e) => {
            if (e.button !== 0 || e.target.closest('button')) return;
            if (pipEl.classList.contains('paint-room-pip--tam-mod')) return;
            dragging = true;
            handle.setPointerCapture(e.pointerId);

            const rect = pipEl.getBoundingClientRect();
            pipW = rect.width;
            pipH = rect.height;
            anchorLeft = rect.left;
            anchorTop = rect.top;
            offsetX = e.clientX - rect.left;
            offsetY = e.clientY - rect.top;
            lastLeft = anchorLeft;
            lastTop = anchorTop;

            pipEl.style.left = `${anchorLeft}px`;
            pipEl.style.top = `${anchorTop}px`;
            pipEl.style.right = 'auto';
            pipEl.style.bottom = 'auto';
            pipEl.style.transform = 'translate3d(0,0,0)';
            pipEl.classList.add('paint-room-pip--grabbing');
            e.preventDefault();
        });

        handle.addEventListener('pointermove', (e) => {
            if (!dragging) return;
            moveByPointer(e.clientX, e.clientY);
        });

        const endDrag = () => {
            if (!dragging) return;
            dragging = false;
            pipEl.classList.remove('paint-room-pip--grabbing');
            commitPosition(lastLeft, lastTop, true);
        };

        handle.addEventListener('pointerup', endDrag);
        handle.addEventListener('pointercancel', endDrag);

        window.addEventListener('resize', () => {
            if (!pipEl.classList.contains('paint-room-pip--dragged')) return;
            const rect = pipEl.getBoundingClientRect();
            pipW = rect.width;
            pipH = rect.height;
            commitPosition(rect.left, rect.top, true);
        });

        restorePosition();

        return { restorePosition };
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatChatTime(iso) {
        if (!iso) return '';
        const date = new Date(iso);
        if (Number.isNaN(date.getTime())) return '';
        return date.toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' });
    }

    function updateChatEmptyState() {
        if (!chatEmptyEl || !chatMessagesEl) return;
        const hasMessages = chatMessagesEl.childElementCount > 0;
        chatEmptyEl.classList.toggle('hidden', hasMessages);
    }

    function scrollChatToBottom() {
        if (!chatMessagesEl) return;
        chatMessagesEl.scrollTop = chatMessagesEl.scrollHeight;
    }

    function updateChatBadge() {
        if (!chatBadge || !chatToggleBtn) return;
        if (chatUnread > 0 && !chatOpen) {
            chatBadge.textContent = chatUnread > 9 ? '9+' : String(chatUnread);
            chatBadge.classList.remove('hidden');
            chatToggleBtn.classList.add('paint-room-pip__btn--chat-unread');
        } else {
            chatBadge.classList.add('hidden');
            chatToggleBtn.classList.remove('paint-room-pip__btn--chat-unread');
        }
    }

    function setChatOpen(open) {
        chatOpen = open;
        chatPanel?.classList.toggle('hidden', !open);
        document.getElementById('paint-room-pip')?.classList.toggle('paint-room-pip--chat-open', open);
        chatToggleBtn?.setAttribute('aria-pressed', open ? 'true' : 'false');
        if (open) {
            chatUnread = 0;
            updateChatBadge();
            scrollChatToBottom();
            chatInput?.focus();
        }
    }

    function appendChatMessage(msg) {
        if (!chatMessagesEl || !msg?.id || seenChatIds.has(msg.id)) return false;
        seenChatIds.add(msg.id);
        lastChatId = Math.max(lastChatId, msg.id);

        const isOwn = msg.from === role;
        const bubble = document.createElement('div');
        bubble.className = `paint-room-chat__msg${isOwn ? ' paint-room-chat__msg--own' : ''}`;
        bubble.dataset.id = String(msg.id);
        bubble.innerHTML = `
            <div class="paint-room-chat__meta">
                <span class="paint-room-chat__name">${escapeHtml(msg.name || (isOwn ? chatDisplayName : 'Karşı taraf'))}</span>
                <time class="paint-room-chat__time">${escapeHtml(formatChatTime(msg.at))}</time>
            </div>
            <p class="paint-room-chat__text">${escapeHtml(msg.text || '')}</p>
        `;
        chatMessagesEl.appendChild(bubble);
        updateChatEmptyState();
        return true;
    }

    async function loadChatHistory() {
        if (!chatHistoryUrl || chatHistoryLoaded) return;
        try {
            const res = await fetch(chatHistoryUrl, {
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) return;
            (data.messages || []).forEach((msg) => appendChatMessage(msg));
            chatHistoryLoaded = true;
            scrollChatToBottom();
        } catch (_) { /* sessiz */ }
    }

    async function pollChat() {
        if (!chatPollUrl || !csrf || participantCount < 2) return;

        const body = new FormData();
        body.append('_token', csrf);
        body.append('after', String(lastChatId));

        try {
            const res = await fetch(chatPollUrl, {
                method: 'POST',
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
                body,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) return;

            let added = false;
            for (const msg of data.messages || []) {
                if (appendChatMessage(msg)) {
                    added = true;
                    if (!chatOpen && msg.from !== role) {
                        chatUnread += 1;
                    }
                }
            }
            if (added) {
                updateChatBadge();
                if (chatOpen) scrollChatToBottom();
            }
        } catch (_) { /* sessiz */ }
    }

    function startChatPolling() {
        if (chatPollTimer || !chatPollUrl) return;
        pollChat();
        chatPollTimer = setInterval(pollChat, 700);
    }

    function stopChatPolling() {
        clearInterval(chatPollTimer);
        chatPollTimer = null;
    }

    async function sendChatMessage(text) {
        const trimmed = text.trim();
        if (!trimmed || !chatSendUrl || !csrf || participantCount < 2) return;

        chatInput && (chatInput.disabled = true);
        chatSendBtn && (chatSendBtn.disabled = true);

        try {
            const body = new FormData();
            body.append('_token', csrf);
            body.append('text', trimmed);

            const res = await fetch(chatSendUrl, {
                method: 'POST',
                headers: authHeaders(),
                credentials: 'same-origin',
                cache: 'no-store',
                body,
            });
            const data = await res.json().catch(() => ({}));
            if (res.status === 419) throw new Error('Oturum süresi doldu — sayfayı yenileyin.');
            if (!res.ok) throw new Error(data.message || 'Mesaj gönderilemedi');

            if (data.message) appendChatMessage(data.message);
            if (chatInput) chatInput.value = '';
            scrollChatToBottom();
        } catch (err) {
            if (chatInput) chatInput.value = trimmed;
            setWebrtcStatus(err?.message || 'Mesaj gönderilemedi');
        } finally {
            if (chatInput) chatInput.disabled = false;
            if (chatSendBtn) chatSendBtn.disabled = false;
            chatInput?.focus();
        }
    }

    chatToggleBtn?.addEventListener('click', () => {
        setChatOpen(!chatOpen);
    });

    chatForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        if (!chatInput) return;
        sendChatMessage(chatInput.value);
    });

    if (participantCount >= 2) {
        startChatPolling();
        loadChatHistory();
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
    const pipFocus = document.getElementById('paint-room-pip-focus');
    const pipDrag = initPipDrag(pipEl);
    const isMobileLayout = window.matchMedia('(max-width: 1023px)').matches;

    if (isMobileLayout && pipEl && !pipEl.classList.contains('paint-room-pip--tam-mod')) {
        pipEl.classList.add('paint-room-pip--collapsed');
        if (pipToggle) pipToggle.textContent = '+';
    }

    document.querySelectorAll('[data-paint-room-toast]').forEach((toast) => {
        window.setTimeout(() => toast.remove(), 4500);
    });

    function resetPipFloatPosition() {
        if (!pipEl) return;
        pipEl.style.left = '';
        pipEl.style.top = '';
        pipEl.style.right = '';
        pipEl.style.bottom = '';
        pipEl.style.transform = '';
        pipDrag?.restorePosition();
    }

    function setTamMod(active) {
        root.classList.toggle('paint-room-studio--tam-mod', active);
        document.body.classList.toggle('paint-room-tam-mod-active', active);
        pipEl?.classList.toggle('paint-room-pip--tam-mod', active);

        if (active) {
            pipEl?.classList.remove('paint-room-pip--expanded', 'paint-room-pip--collapsed');
            pipEl.style.left = '';
            pipEl.style.top = '';
            pipEl.style.right = '';
            pipEl.style.bottom = '';
            pipEl.style.transform = '';
        } else {
            resetPipFloatPosition();
        }

        if (pipFocus) {
            pipFocus.textContent = active ? '⊟' : '⊞';
            pipFocus.title = active ? 'Küçük görünüme dön' : 'Tam mod — boyama ve görüntülü';
            pipFocus.setAttribute('aria-pressed', active ? 'true' : 'false');
        }
        if (pipToggle) {
            pipToggle.textContent = pipEl?.classList.contains('paint-room-pip--collapsed') ? '+' : '−';
        }
    }

    pipToggle?.addEventListener('click', () => {
        if (pipEl?.classList.contains('paint-room-pip--tam-mod')) return;
        pipEl?.classList.toggle('paint-room-pip--collapsed');
        pipToggle.textContent = pipEl?.classList.contains('paint-room-pip--collapsed') ? '+' : '−';
    });
    pipExpand?.addEventListener('click', () => {
        if (pipEl?.classList.contains('paint-room-pip--tam-mod')) return;
        pipEl?.classList.toggle('paint-room-pip--expanded');
    });
    pipFocus?.addEventListener('click', () => {
        setTamMod(!root.classList.contains('paint-room-studio--tam-mod'));
    });

    window.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && root.classList.contains('paint-room-studio--tam-mod')) {
            setTamMod(false);
        }
    });

    const infoPanel = document.getElementById('paint-room-info-panel');
    const infoToggle = document.getElementById('paint-room-info-toggle');
    const infoClose = document.getElementById('paint-room-info-close');
    const pageToggle = document.getElementById('paint-room-page-toggle');
    const lobbyBrowser = document.getElementById('paint-room-lobby-browser');

    pageToggle?.addEventListener('click', () => {
        document.getElementById('paint-room-page-panel')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    });

    lobbyBrowser?.addEventListener('paint-room:page-selected', (e) => {
        if (role !== 'owner') return;
        changeColoringPage(e.detail?.pageId, e.detail?.title || '');
    });

    if (currentColoringPageId) {
        setActivePickerItem(currentColoringPageId);
    }

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

    document.getElementById('paint-room-mobile-zoom-fit')?.addEventListener('click', () => {
        canvasApi?.fitToView?.();
    });

    initLocalMedia();
})();

/**
 * Görüntülü boyama odası — MVP bekleme lobisi (polling)
 */
(function () {
    const root = document.getElementById('paint-room-lobby');
    if (!root) return;

    const statusUrl = root.dataset.statusUrl;
    const leaveUrl = root.dataset.leaveUrl;
    const indexUrl = root.dataset.indexUrl;
    const role = root.dataset.role;
    const expiresAt = new Date(root.dataset.expiresAt);
    const countEl = document.getElementById('paint-room-count');
    const statusText = document.getElementById('paint-room-status-text');
    const timerEl = document.getElementById('paint-room-timer');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

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
        if (diff <= 0) {
            window.location.href = indexUrl + '?expired=1';
        }
    }

    async function pollStatus() {
        try {
            const res = await fetch(statusUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            const data = await res.json();
            if (!data.open) {
                window.location.href = indexUrl;
                return;
            }
            if (countEl) countEl.textContent = String(data.participants);
            if (statusText) statusText.textContent = data.message;
        } catch (_) {
            /* sessiz */
        }
    }

    function sendLeaveBeacon() {
        if (role !== 'owner' || !leaveUrl) return;
        const body = new URLSearchParams({ _token: csrf });
        if (navigator.sendBeacon) {
            navigator.sendBeacon(leaveUrl, body);
        }
    }

    if (role === 'owner') {
        window.addEventListener('pagehide', sendLeaveBeacon);
        window.addEventListener('beforeunload', sendLeaveBeacon);
    }

    updateTimer();
    setInterval(updateTimer, 1000);
    pollStatus();
    setInterval(pollStatus, 2500);
})();

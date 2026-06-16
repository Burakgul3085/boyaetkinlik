/**
 * Görüntülü boyama — kategorili ücretsiz sayfa seçici
 */
export function initPaintRoomPageBrowser(root) {
    if (!root) return null;

    let tree = [];
    try {
        tree = JSON.parse(root.dataset.categoryTree || '[]');
    } catch (_) {
        tree = [];
    }

    const pagesUrl = root.dataset.pagesUrl || '';
    const selectedId = root.dataset.selectedPageId || '';
    const compact = root.dataset.compact === '1';

    const breadcrumbEl = root.querySelector('[data-browser-breadcrumb]');
    const categoriesEl = root.querySelector('[data-browser-categories]');
    const pagesEl = root.querySelector('[data-browser-pages]');
    const statusEl = root.querySelector('[data-browser-status]');

    const byId = new Map();
    const parentOf = new Map();

    function indexNodes(nodes, parentId = null) {
        nodes.forEach((node) => {
            byId.set(node.id, node);
            parentOf.set(node.id, parentId);
            indexNodes(node.children || [], node.id);
        });
    }

    indexNodes(tree);

    let path = [];
    let activePageId = selectedId ? String(selectedId) : '';
    let loadingPages = false;

    function currentNode() {
        if (path.length === 0) return null;
        return byId.get(path[path.length - 1]) || null;
    }

    function nodesAtLevel() {
        if (path.length === 0) return tree;
        const current = currentNode();
        return current?.children || [];
    }

    function setStatus(text) {
        if (statusEl) statusEl.textContent = text;
    }

    function setActivePage(pageId, title) {
        activePageId = String(pageId);
        root.querySelectorAll('.paint-room-page-picker__item').forEach((btn) => {
            const isActive = btn.dataset.pageId === activePageId;
            btn.classList.toggle('paint-room-page-picker__item--active', isActive);
            btn.disabled = isActive;
        });
        root.dispatchEvent(new CustomEvent('paint-room:page-selected', {
            bubbles: true,
            detail: { pageId: activePageId, title: title || '' },
        }));
    }

    function renderBreadcrumb() {
        if (!breadcrumbEl) return;
        const parts = [{ id: null, name: 'Kategoriler' }];
        path.forEach((id) => {
            const node = byId.get(id);
            if (node) parts.push({ id, name: node.name });
        });

        breadcrumbEl.innerHTML = '';
        parts.forEach((part, index) => {
            if (index > 0) {
                const sep = document.createElement('span');
                sep.className = 'paint-room-page-browser__crumb-sep';
                sep.textContent = '›';
                breadcrumbEl.appendChild(sep);
            }
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'paint-room-page-browser__crumb';
            if (index === parts.length - 1) {
                btn.classList.add('paint-room-page-browser__crumb--current');
            }
            btn.textContent = part.name;
            btn.addEventListener('click', () => {
                if (part.id === null) {
                    path = [];
                } else {
                    const idx = path.indexOf(part.id);
                    path = idx >= 0 ? path.slice(0, idx + 1) : [];
                }
                render();
            });
            breadcrumbEl.appendChild(btn);
        });
    }

    function renderCategories() {
        if (!categoriesEl) return;
        const nodes = nodesAtLevel();
        categoriesEl.innerHTML = '';

        if (nodes.length === 0) {
            categoriesEl.classList.add('hidden');
            return;
        }

        categoriesEl.classList.remove('hidden');
        const label = document.createElement('p');
        label.className = 'paint-room-page-browser__section-label';
        label.textContent = path.length === 0 ? 'Ana kategoriler' : 'Alt kategoriler';
        categoriesEl.appendChild(label);

        const row = document.createElement('div');
        row.className = 'paint-room-page-browser__cat-row';

        nodes.forEach((node) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'paint-room-page-browser__cat-btn';
            btn.innerHTML = `
                <span class="paint-room-page-browser__cat-name">${escapeHtml(node.name)}</span>
                <span class="paint-room-page-browser__cat-count">${node.totalCount}</span>
            `;
            btn.addEventListener('click', () => {
                path.push(node.id);
                render();
            });
            row.appendChild(btn);
        });

        categoriesEl.appendChild(row);
    }

    async function renderPages() {
        if (!pagesEl) return;
        const node = currentNode();

        if (!node) {
            pagesEl.innerHTML = '';
            setStatus('Bir kategori seçin; ardından boyama görünecek.');
            return;
        }

        if (!pagesUrl) {
            setStatus('Sayfa listesi yüklenemedi.');
            return;
        }

        loadingPages = true;
        setStatus('Boyamalar yükleniyor…');
        pagesEl.innerHTML = '<p class="paint-room-page-browser__loading">Yükleniyor…</p>';

        try {
            const res = await fetch(`${pagesUrl}?category_id=${node.id}`, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                cache: 'no-store',
            });
            const data = await res.json().catch(() => ({}));
            const pages = data.pages || [];

            pagesEl.innerHTML = '';
            if (pages.length === 0) {
                const empty = document.createElement('p');
                empty.className = 'paint-room-page-browser__empty';
                empty.textContent = node.children?.length
                    ? 'Bu kategoride doğrudan boyama yok — alt kategoriye girin.'
                    : 'Bu kategoride ücretsiz boyama yok.';
                pagesEl.appendChild(empty);
                setStatus(node.name);
                return;
            }

            const label = document.createElement('p');
            label.className = 'paint-room-page-browser__section-label';
            label.textContent = `${node.name} — ${pages.length} boyama`;
            pagesEl.appendChild(label);

            const grid = document.createElement('div');
            grid.className = compact
                ? 'paint-room-page-picker paint-room-page-picker--compact'
                : 'paint-room-page-picker';

            pages.forEach((page) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'paint-room-page-picker__item';
                if (String(page.id) === activePageId) {
                    btn.classList.add('paint-room-page-picker__item--active');
                    btn.disabled = true;
                }
                btn.dataset.pageId = String(page.id);
                btn.dataset.pageTitle = page.title || '';
                btn.title = page.title || '';
                btn.innerHTML = `
                    <span class="paint-room-page-picker__thumb">
                        <img src="${page.previewUrl}" alt="" loading="lazy" width="120" height="150">
                    </span>
                    <span class="paint-room-page-picker__label">${escapeHtml(page.title || '')}</span>
                `;
                btn.addEventListener('click', () => setActivePage(page.id, page.title));
                grid.appendChild(btn);
            });

            pagesEl.appendChild(grid);
            setStatus(pages.length ? `${node.name} — boyama seçin` : node.name);
        } catch (_) {
            pagesEl.innerHTML = '<p class="paint-room-page-browser__empty">Boyamalar yüklenemedi.</p>';
            setStatus('Yükleme hatası — tekrar deneyin.');
        } finally {
            loadingPages = false;
        }
    }

    function escapeHtml(text) {
        return String(text)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function render() {
        renderBreadcrumb();
        renderCategories();
        renderPages();
    }

    render();

    return {
        setSelectedPageId(pageId) {
            activePageId = pageId ? String(pageId) : '';
            root.querySelectorAll('.paint-room-page-picker__item').forEach((btn) => {
                const isActive = btn.dataset.pageId === activePageId;
                btn.classList.toggle('paint-room-page-picker__item--active', isActive);
                btn.disabled = isActive;
            });
        },
    };
}

function bootPaintRoomPageBrowsers() {
    document.querySelectorAll('[data-paint-room-page-browser]').forEach((root) => {
        if (root.dataset.browserReady === '1') return;
        initPaintRoomPageBrowser(root);
        root.dataset.browserReady = '1';
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootPaintRoomPageBrowsers);
} else {
    bootPaintRoomPageBrowsers();
}

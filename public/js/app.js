/* ==========================================================================
   CutURL — vanilla JavaScript. No dependencies, no build step.
   Handles: theme toggle, copy-to-clipboard, delete/clear confirmation,
   and live client-side dashboard filtering.
   ========================================================================== */

(function () {
    'use strict';

    /* ---------------------------------------------------------------- Theme */
    const THEME_KEY = 'cuturl-theme';
    const root = document.documentElement;

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        try { localStorage.setItem(THEME_KEY, theme); } catch (e) { /* ignore */ }
    }

    (function initTheme() {
        let saved = null;
        try { saved = localStorage.getItem(THEME_KEY); } catch (e) { /* ignore */ }
        if (saved) {
            root.setAttribute('data-theme', saved);
        } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
            root.setAttribute('data-theme', 'light');
        }
    })();

    const themeToggle = document.getElementById('theme-toggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const current = root.getAttribute('data-theme') === 'light' ? 'light' : 'dark';
            applyTheme(current === 'light' ? 'dark' : 'light');
        });
    }

    /* ------------------------------------------------------------- Copy URL */
    async function copyText(text) {
        // Prefer the async clipboard API, fall back to a hidden textarea.
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(text);
                return true;
            } catch (e) { /* fall through */ }
        }
        try {
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.setAttribute('readonly', '');
            ta.style.position = 'absolute';
            ta.style.left = '-9999px';
            document.body.appendChild(ta);
            ta.select();
            const ok = document.execCommand('copy');
            document.body.removeChild(ta);
            return ok;
        } catch (e) {
            return false;
        }
    }

    function flashCopied(button) {
        button.classList.add('is-copied');
        const label = button.querySelector('.copy-label');
        const original = label ? label.textContent : null;
        if (label) { label.textContent = 'Copied'; }
        window.setTimeout(function () {
            button.classList.remove('is-copied');
            if (label && original !== null) { label.textContent = original; }
        }, 1600);
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.btn-copy');
        if (!button) { return; }
        event.preventDefault();

        let value = button.getAttribute('data-copy-value');
        if (!value) {
            const targetSel = button.getAttribute('data-copy-target');
            const target = targetSel ? document.querySelector(targetSel) : null;
            if (target) { value = target.value || target.textContent; }
        }
        if (!value) { return; }

        copyText(value).then(function (ok) {
            if (ok) { flashCopied(button); }
        });
    });

    /* --------------------------------------------- Confirm destructive forms */
    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (form.matches('[data-confirm]')) {
            const message = form.getAttribute('data-confirm');
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        }
    });

    /* ---------------------------------------- Live dashboard filtering (JS) */
    const searchInput = document.getElementById('dashboard-search');
    const searchable = document.querySelector('[data-searchable]');

    if (searchInput && searchable) {
        const rows = Array.prototype.slice.call(searchable.querySelectorAll('[data-row]'));
        const noResults = searchable.querySelector('[data-no-results]');

        searchInput.addEventListener('input', function () {
            const term = searchInput.value.trim().toLowerCase();
            let visible = 0;

            rows.forEach(function (row) {
                const haystack = row.getAttribute('data-search') || '';
                const match = term === '' || haystack.indexOf(term) !== -1;
                row.hidden = !match;
                if (match) { visible++; }
            });

            if (noResults) { noResults.hidden = visible !== 0; }
        });
    }
})();

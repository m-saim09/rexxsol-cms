const initializeApp = () => {
    const globalSearch = document.querySelector('[data-global-search]');

    const isEditableTarget = (target) => {
        return target instanceof HTMLElement && (
            target.matches('input, textarea, select, [contenteditable="true"]') ||
            Boolean(target.closest('input, textarea, select, [contenteditable="true"]'))
        );
    };

    const isInsideDialog = (target) => {
        return target instanceof HTMLElement && Boolean(target.closest('dialog, [role="dialog"], [data-modal]'));
    };

    document.addEventListener('keydown', (event) => {
        if (event.altKey && !event.ctrlKey && !event.metaKey && event.key.toLowerCase() === 'k') {
            if (!globalSearch || isEditableTarget(event.target) || isInsideDialog(event.target) || isInsideDialog(document.activeElement)) {
                return;
            }

            event.preventDefault();
            globalSearch.focus({ preventScroll: true });
            globalSearch.select();
        }

        if (event.key === 'Escape' && globalSearch === document.activeElement) {
            globalSearch.blur();
        }
    });

    document.querySelectorAll('[data-nav-details]').forEach((details) => {
        const summary = details.querySelector('.site-mobile-toggle');

        if (!summary) {
            return;
        }

        const syncToggleState = () => {
            summary.setAttribute('aria-expanded', details.open ? 'true' : 'false');
        };

        syncToggleState();
        details.addEventListener('toggle', syncToggleState);
    });

    document.querySelectorAll('[data-rotating-words]').forEach((element) => {
        const words = (element.dataset.rotatingWords || '').split('|').filter(Boolean);

        if (words.length <= 1) {
            return;
        }

        let index = 0;

        const updateWord = () => {
            index = (index + 1) % words.length;
            element.textContent = words[index];
        };

        element.textContent = words[0];
        element.dataset.originalText = words[0];
        setInterval(updateWord, 1800);
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.getElementById(button.dataset.passwordToggle || '');

        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            button.classList.toggle('is-visible', isPassword);
            button.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
        });
    });
};

window.customCssEditor = (initialValue) => ({
    source: initialValue || '',
    cursor: 0,
    get lineCount() {
        return Math.max(1, this.source.split('\n').length);
    },
    get highlighted() {
        const tokenPattern = /(\/\*[\s\S]*?\*\/)|("(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*')|(@[\w-]+)|(--[\w-]+)|(\b[\w-]+(?=\s*:))|(\b\d+(?:\.\d+)?(?:px|em|rem|vh|vw|%|s|ms|deg)?\b)|([{}()[\]:;,])/gi;
        const escapeHtml = (value) => value.replace(/[&<>"']/g, (character) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        })[character]);
        const classes = ['text-[#64748b] italic', 'text-[#059669]', 'text-[#7c3aed]', 'text-[#b45309]', 'text-[#2563eb]', 'text-[#0891b2]', 'text-[#94a3b8]'];
        const pairIndexes = this.matchingPair();
        let output = '';
        let cursor = 0;

        for (const match of this.source.matchAll(tokenPattern)) {
            const token = match[0];
            const offset = match.index ?? 0;
            const group = match.slice(1).findIndex((value) => value !== undefined);
            output += escapeHtml(this.source.slice(cursor, offset));
            const activePair = pairIndexes.includes(offset);
            output += `<span class="${classes[group]}${activePair ? ' rounded bg-blue-100 outline outline-1 outline-blue-300' : ''}">${escapeHtml(token)}</span>`;
            cursor = offset + token.length;
        }

        return output + escapeHtml(this.source.slice(cursor));
    },
    matchingPair() {
        const pattern = /\/\*[\s\S]*?\*\/|"(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'/g;
        const ignored = new Set();
        for (const match of this.source.matchAll(pattern)) {
            for (let index = match.index; index < match.index + match[0].length; index++) ignored.add(index);
        }

        const stack = [];
        const pairs = new Map();
        const closingToOpening = { ')': '(', ']': '[', '}': '{' };
        for (let index = 0; index < this.source.length; index++) {
            if (ignored.has(index)) continue;
            const character = this.source[index];
            if ('([{'.includes(character)) {
                stack.push([character, index]);
            } else if (closingToOpening[character]) {
                const last = stack.at(-1);
                if (last?.[0] === closingToOpening[character]) {
                    stack.pop();
                    pairs.set(index, last[1]);
                    pairs.set(last[1], index);
                }
            }
        }

        for (const position of [this.cursor, this.cursor - 1]) {
            if (pairs.has(position)) return [position, pairs.get(position)];
        }

        return [];
    },
    updateCursor(event) {
        this.cursor = event.target.selectionStart;
    },
    syncScroll(event) {
        const overlay = event.target.parentElement.querySelector('[data-css-highlight]');
        const numbers = event.target.closest('[data-css-editor]').querySelector('[data-css-lines]');
        overlay.scrollTop = event.target.scrollTop;
        overlay.scrollLeft = event.target.scrollLeft;
        numbers.scrollTop = event.target.scrollTop;
    },
    indent(event) {
        if (event.key !== 'Tab') {
            this.$nextTick(() => this.updateCursor(event));
            return;
        }

        event.preventDefault();
        const input = event.target;
        const start = input.selectionStart;
        const end = input.selectionEnd;
        this.source = `${this.source.slice(0, start)}    ${this.source.slice(end)}`;
        this.$nextTick(() => {
            input.selectionStart = input.selectionEnd = start + 4;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            this.cursor = start + 4;
        });
    },
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeApp, { once: true });
} else {
    initializeApp();
}

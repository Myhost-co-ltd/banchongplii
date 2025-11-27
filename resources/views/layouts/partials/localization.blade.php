<script>
(function () {
    const LANG_KEY = 'appLocale';

    function applyLanguage(lang) {
        const isThai = lang === 'th';
        document.documentElement.setAttribute('lang', lang);
        localStorage.setItem(LANG_KEY, lang);

        document.querySelectorAll('[data-lang-toggle]').forEach(btn => {
            const labelEl = btn.querySelector('[data-lang-label]');
            if (labelEl) {
                labelEl.textContent = lang.toUpperCase();
            }
            const ariaText = isThai ? 'เปลี่ยนเป็นภาษาอังกฤษ' : 'Switch to Thai';
            btn.setAttribute('aria-label', ariaText);
            btn.setAttribute('title', ariaText);
        });

        document.querySelectorAll('[data-i18n-th]').forEach(el => {
            const text = isThai ? el.dataset.i18nTh : (el.dataset.i18nEn || el.dataset.i18nTh);
            if (text !== undefined) {
                el.textContent = text;
            }
        });

        document.querySelectorAll('[data-i18n-placeholder-th]').forEach(el => {
            const text = isThai
                ? el.dataset.i18nPlaceholderTh
                : (el.dataset.i18nPlaceholderEn || el.dataset.i18nPlaceholderTh);
            if (text !== undefined) {
                el.setAttribute('placeholder', text);
            }
        });

        document.querySelectorAll('[data-i18n-title-th]').forEach(el => {
            const text = isThai
                ? el.dataset.i18nTitleTh
                : (el.dataset.i18nTitleEn || el.dataset.i18nTitleTh);
            if (text !== undefined) {
                el.setAttribute('title', text);
            }
        });

        document.querySelectorAll('[data-i18n-aria-th]').forEach(el => {
            const text = isThai
                ? el.dataset.i18nAriaTh
                : (el.dataset.i18nAriaEn || el.dataset.i18nAriaTh);
            if (text !== undefined) {
                el.setAttribute('aria-label', text);
            }
        });
    }

    function initLanguage() {
        const stored = localStorage.getItem(LANG_KEY) || document.documentElement.getAttribute('lang') || 'th';
        applyLanguage(stored);

        document.querySelectorAll('[data-lang-toggle]').forEach(btn => {
            btn.addEventListener('click', () => {
                const current = localStorage.getItem(LANG_KEY) || 'th';
                const next = current === 'th' ? 'en' : 'th';
                applyLanguage(next);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLanguage);
    } else {
        initLanguage();
    }
})();
</script>

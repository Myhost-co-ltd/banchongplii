<script>
  (() => {
    const STORAGE_KEY = 'app:scroll-restore:v1';
    const TTL_MS = 15000;
    const locationKey = `${window.location.pathname}${window.location.search}`;

    const getReferrerKey = () => {
      if (!document.referrer) {
        return '';
      }

      try {
        const referrer = new URL(document.referrer);
        if (referrer.origin !== window.location.origin) {
          return '';
        }

        return `${referrer.pathname}${referrer.search}`;
      } catch (error) {
        return '';
      }
    };

    const getScrollTarget = () => {
      const marked = document.querySelector('[data-scroll-container]');
      if (marked) {
        return marked;
      }

      const selectors = [
        'main > .overflow-y-auto',
        'main > div > .overflow-y-auto',
        'main > div > div.overflow-y-auto',
      ];

      for (const selector of selectors) {
        const match = document.querySelector(selector);
        if (match) {
          return match;
        }
      }

      return document.scrollingElement || document.documentElement;
    };

    const isWindowScroll = (target) =>
      !target ||
      target === document.body ||
      target === document.documentElement ||
      target === document.scrollingElement;

    const readScrollTop = (target) => {
      if (isWindowScroll(target)) {
        return window.scrollY || window.pageYOffset || 0;
      }

      return target.scrollTop;
    };

    const writeScrollTop = (target, top) => {
      const nextTop = Math.max(0, Number(top) || 0);

      if (isWindowScroll(target)) {
        window.scrollTo({ top: nextTop, behavior: 'auto' });
        return;
      }

      target.scrollTop = nextTop;
    };

    const saveScrollPosition = () => {
      const payload = {
        key: locationKey,
        top: readScrollTop(getScrollTarget()),
        at: Date.now(),
      };

      sessionStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
    };

    const restoreScrollPosition = () => {
      const raw = sessionStorage.getItem(STORAGE_KEY);
      if (!raw) {
        return;
      }

      let payload;
      try {
        payload = JSON.parse(raw);
      } catch (error) {
        sessionStorage.removeItem(STORAGE_KEY);
        return;
      }

      const age = Date.now() - Number(payload?.at || 0);
      const referrerKey = getReferrerKey();

      if (age > TTL_MS) {
        sessionStorage.removeItem(STORAGE_KEY);
        return;
      }

      if ((payload?.key || '') !== locationKey) {
        if ((payload?.key || '') === referrerKey) {
          sessionStorage.removeItem(STORAGE_KEY);
        }
        return;
      }

      sessionStorage.removeItem(STORAGE_KEY);

      const apply = () => {
        writeScrollTop(getScrollTarget(), payload.top);
      };

      requestAnimationFrame(() => {
        requestAnimationFrame(apply);
      });
      window.addEventListener('load', apply, { once: true });
    };

    document.addEventListener('submit', (event) => {
      const form = event.target;
      if (!(form instanceof HTMLFormElement)) {
        return;
      }

      if (form.hasAttribute('data-no-scroll-restore')) {
        return;
      }

      const method = (form.getAttribute('method') || form.method || 'get').toLowerCase();
      if (method === 'get') {
        return;
      }

      saveScrollPosition();
    }, true);

    document.addEventListener('DOMContentLoaded', restoreScrollPosition);
  })();
</script>

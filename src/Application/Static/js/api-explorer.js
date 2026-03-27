document.addEventListener('DOMContentLoaded', () => {
  const explorers = document.querySelectorAll('[data-api-explorer]');

  explorers.forEach((explorer) => {
    const buttons = explorer.querySelectorAll('[data-api-request]');
    const titleEl = explorer.querySelector('[data-api-response-title]');
    const statusEl = explorer.querySelector('[data-api-response-status]');
    const typeEl = explorer.querySelector('[data-api-response-type]');
    const bodyEl = explorer.querySelector('[data-api-response-body]');
    const headersEl = explorer.querySelector('[data-api-request-headers]');

    if (!buttons.length || !titleEl || !statusEl || !typeEl || !bodyEl || !headersEl) {
      return;
    }

    const setActive = (activeButton) => {
      buttons.forEach((button) => button.classList.toggle('is-active', button === activeButton));
    };

    const runRequest = async (button) => {
      const url = button.getAttribute('data-url');
      const method = button.getAttribute('data-method') || 'GET';
      const labelEl = button.querySelector('.api-explorer__label');
      const headersRaw = button.getAttribute('data-headers') || '{}';

      let headers = {};
      try {
        headers = JSON.parse(headersRaw);
      } catch (_error) {
        headers = {};
      }

      setActive(button);
      titleEl.textContent = labelEl ? labelEl.textContent : method + ' ' + url;
      headersEl.textContent = JSON.stringify(headers, null, 2);
      statusEl.textContent = '...';
      typeEl.textContent = 'loading';
      bodyEl.textContent = 'Fetching live response...';

      try {
        const response = await fetch(url, {
          method,
          headers,
          credentials: 'same-origin',
        });

        const contentType = response.headers.get('content-type') || 'unknown';
        const text = await response.text();
        let prettyText = text;

        try {
          prettyText = JSON.stringify(JSON.parse(text), null, 2);
        } catch (_error) {
          // keep raw text when the response is not JSON
        }

        statusEl.textContent = String(response.status);
        typeEl.textContent = contentType;
        bodyEl.textContent = prettyText;
      } catch (error) {
        statusEl.textContent = 'ERR';
        typeEl.textContent = 'request failed';
        bodyEl.textContent = error instanceof Error ? error.message : 'Request failed.';
      }
    };

    buttons.forEach((button) => {
      button.addEventListener('click', () => {
        void runRequest(button);
      });
    });
  });
});

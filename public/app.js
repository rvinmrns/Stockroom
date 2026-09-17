(() => {
  let busy = false;
  function notice(message, error = false) {
    document.querySelector('#request-notice')?.remove();
    const node = document.createElement('div');
    node.id = 'request-notice';
    node.className = `notice ${error ? 'error' : 'success'}`;
    node.setAttribute('role', error ? 'alert' : 'status');
    node.textContent = message;
    const container = document.querySelector('.content, .login-panel, .accounts-page') || document.body;
    container.prepend(node);
    node.scrollIntoView({ block: 'nearest' });
  }
  async function request(url, options = {}) {
    const response = await fetch(url, {
      ...options, credentials: 'same-origin',
      headers: { Accept: 'application/json', ...options.headers }
    });
    if (!response.headers.get('content-type')?.includes('application/json')) {
      throw new Error('Unexpected server response. Check your connection and try again.');
    }
    const result = await response.json();
    if (!response.ok || !result.ok) throw new Error(result.message || 'Request failed.');
    return result;
  }
  async function navigate(url, historyMode = 'push') {
    const result = await request(url);
    if (typeof result.html !== 'string') throw new Error('The page could not be loaded.');
    const page = new DOMParser().parseFromString(result.html, 'text/html');
    // Scripts stay loaded once; event delegation also handles newly rendered forms.
    page.querySelectorAll('script').forEach(script => script.remove());
    document.body.className = page.body.className;
    document.body.replaceChildren(...page.body.childNodes);
    document.title = page.title;
    if (historyMode === 'push') history.pushState(null, '', url);
    if (historyMode === 'replace') history.replaceState(null, '', url);
  }
  function setBusy(value) {
    busy = value;
    document.documentElement.setAttribute('aria-busy', String(value));
  }
  document.addEventListener('submit', async event => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;
    event.preventDefault();
    if (busy) return;
    if (form.matches('.delete-form') && !confirm('Delete this product permanently?')) return;
    const fields = new FormData(form);
    if (event.submitter?.name) fields.set(event.submitter.name, event.submitter.value);
    setBusy(true);
    let saved = false;
    try {
      // A field named "action" shadows form.action in browsers.
      const url = new URL(form.getAttribute('action') || location.href, location.href);
      if (form.method.toLowerCase() === 'get') {
        url.search = new URLSearchParams(fields).toString();
        await navigate(url);
      } else {
        const result = await request(url, {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(Object.fromEntries(fields))
        });
        saved = true;
        if (result.csrf) document.querySelectorAll('input[name="csrf"]').forEach(input => { input.value = result.csrf; });
        const destination = new URL(result.url || 'stockroom.php', url);
        await navigate(destination, 'replace');
        if (result.message && result.message !== 'Done.') notice(result.message);
      }
    } catch (error) {
      notice(saved ? 'Your change was saved, but the updated page could not load. Reload before making another change.' : error.message, true);
    } finally { setBusy(false); }
  });
  document.addEventListener('click', async event => {
    const link = event.target.closest('a');
    if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || link.target || link.hasAttribute('download')) return;
    const url = new URL(link.href, location.href);
    if (url.origin !== location.origin || url.searchParams.has('export') || !/\/(stockroom|users|assignments)\.php$/.test(url.pathname)) return;
    event.preventDefault();
    if (busy) return;
    setBusy(true);
    try { await navigate(url); } catch (error) { notice(error.message, true); }
    finally { setBusy(false); }
  });
  window.addEventListener('popstate', () => {
    // Reload the GET entry to avoid racing a mutation or displaying stale permissions.
    location.reload();
  });
})();

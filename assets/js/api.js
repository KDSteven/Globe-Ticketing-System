/**
 * assets/js/api.js
 * Thin fetch wrapper used across the application.
 *
 * - Always sends credentials (session cookie) with every request.
 * - Automatically sets Content-Type: application/json for JSON bodies.
 * - Parses the response as JSON and returns it directly.
 * - Throws an Error (with .status and .data) on non-2xx responses.
 *
 * Usage:
 *   const data = await apiFetch('/api/some.php', { method: 'POST', body: JSON.stringify({...}) });
 *   const list = await apiFetch('/api/list.php');
 */
async function apiFetch(url, options = {}) {
  const method = (options.method || 'GET').toUpperCase();

  const config = {
    credentials: 'same-origin',
    ...options,
  };

  // Auto-set Content-Type for JSON bodies (skip for FormData — browser sets boundary)
  if (method !== 'GET' && method !== 'HEAD' && !(options.body instanceof FormData)) {
    config.headers = {
      'Content-Type': 'application/json',
      ...(options.headers || {}),
    };
  }

  const res = await fetch(url, config);

  if (!res.ok) {
    const err = new Error(`API error ${res.status} on ${method} ${url}`);
    err.status = res.status;
    try { err.data = await res.json(); } catch { /* non-JSON error body */ }
    throw err;
  }

  return res.json();
}

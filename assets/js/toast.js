// Minimal, framework-free toast helper
(() => {
  function escHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, c => (
      {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]
    ));
  }

  const ICONS = {
    success: 'fa-solid fa-check',
    error:   'fa-solid fa-triangle-exclamation',
    info:    'fa-solid fa-circle-info',
    warning: 'fa-solid fa-exclamation'
  };

  function ensureContainer() {
    let c = document.querySelector('.toast-container');
    if (!c) {
      c = document.createElement('div');
      c.className = 'toast-container';
      document.body.appendChild(c);
    }
    return c;
  }

  function makeToast(type, title, message, opts = {}) {
    const container = ensureContainer();

    // Backward compatibility: toast.success('Saved!')
    if (!message && title) { message = title; title = type.charAt(0).toUpperCase() + type.slice(1) + '!'; }
    if (!title) title = type.charAt(0).toUpperCase() + type.slice(1) + '!';
    if (!message) message = '';

    const dur = typeof opts.duration === 'number' ? opts.duration : 3500;

    const el = document.createElement('div');
    el.className = `toast -${type}`;

    el.innerHTML = `
      <span class="toast__accent"></span>
      <div class="toast__body">
        <span class="toast__icon"><i class="${ICONS[type] || ICONS.info}"></i></span>
        <div class="toast__copy">
          <div class="toast__title">${escHtml(title)}</div>
          ${message ? `<div class="toast__msg">${escHtml(message)}</div>` : ``}
        </div>
      </div>
      <button class="toast__close" aria-label="Dismiss">&times;</button>
      <div class="toast__bar"><i style="background: var(--accent); animation-duration:${dur}ms"></i></div>
    `;

    // Close handlers
    const close = () => {
      el.style.animation = 'toast-out .22s ease-in forwards';
      clearTimeout(timer);
      setTimeout(() => el.remove(), 220);
    };
    el.querySelector('.toast__close').addEventListener('click', close);

    // Insert and start timer
    container.appendChild(el);
    const timer = setTimeout(close, dur);

    // Pause on hover
    el.addEventListener('mouseenter', () => clearTimeout(timer));
    el.addEventListener('mouseleave', () => setTimeout(close, 1200));

    return el;
  }

  // Expose global API
  window.toast = {
    success: (title, msg, opts) => makeToast('success', title, msg, opts),
    error:   (title, msg, opts) => makeToast('error',   title, msg, opts),
    info:    (title, msg, opts) => makeToast('info',    title, msg, opts),
    warning: (title, msg, opts) => makeToast('warning', title, msg, opts),
  };
})();


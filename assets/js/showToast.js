document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(location.search);

  const phpAlert = document.querySelector('.alert.error');
  if (phpAlert) phpAlert.style.display = 'none';

  const err = params.get('error');
  const ok  = params.get('ok');

  if (err) toast?.error(err);
  if (ok)  toast?.success(ok);

  if (err || ok) {
    params.delete('error');
    params.delete('ok');
    const qs = params.toString();
    history.replaceState({}, '', location.pathname + (qs ? `?${qs}` : ''));
  }
});

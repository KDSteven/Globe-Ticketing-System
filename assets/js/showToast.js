document.addEventListener('DOMContentLoaded', () => {
  const params = new URLSearchParams(location.search);

  const phpAlert = document.querySelector('.alert.error');
  if (phpAlert) phpAlert.style.display = 'none';   // avoid double message

  const err = params.get('error');
  const ok  = params.get('ok');

  if (err) {
    toast?.error(err);
  } else if (ok) {
    toast?.success(ok);
  }
});

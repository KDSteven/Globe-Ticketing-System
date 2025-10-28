
(function(){
  const oc = document.getElementById('offcanvas');
  const bd = document.getElementById('sbBackdrop');
  const btnOpen  = document.getElementById('sbToggle');
  const btnClose = document.getElementById('sbClose');

  function openSB(){ oc.classList.add('open'); bd.classList.add('show'); oc.setAttribute('aria-hidden','false'); }
  function closeSB(){ oc.classList.remove('open'); bd.classList.remove('show'); oc.setAttribute('aria-hidden','true'); }
  btnOpen?.addEventListener('click', openSB);
  btnClose?.addEventListener('click', closeSB);
  bd?.addEventListener('click', closeSB);
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') closeSB(); });
})();


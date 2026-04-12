// assets/js/remarks-modal.js
// Remarks modal logic for tickets.php.

document.addEventListener('DOMContentLoaded', () => {
  const modal    = document.getElementById('remarksModal');
  if (!modal) return;

  const titleEl  = document.getElementById('remarksModalTitle');
  const idEl     = document.getElementById('remarksTicketId');
  const textEl   = document.getElementById('remarksText');
  const closeBtn = document.getElementById('closeRemarksModal');

  function openModal({ id, ticket, remarks }) {
    idEl.value         = id;
    textEl.value       = remarks || '';
    titleEl.textContent = `Remarks — ${ticket}`;
    modal.style.display = 'flex';
    setTimeout(() => textEl.focus(), 50);
  }

  function closeModal() {
    modal.style.display = 'none';
    idEl.value   = '';
    textEl.value = '';
  }

  // Event delegation — handles dynamically rendered rows too
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.openRemarksModal');
    if (!btn) return;
    openModal({
      id:      btn.dataset.id,
      ticket:  btn.dataset.ticket,
      remarks: btn.dataset.remarks,
    });
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.style.display !== 'none') closeModal();
  });
});

// -------------------------------
// Generic Simple Modal Helper
// -------------------------------
/**
 * Wire up a basic open/close modal by element IDs.
 * Returns false (safely) when any element is missing — safe to call on any page.
 */
function simpleModal(openBtnId, closeBtnId, modalId) {
  const openBtn = document.getElementById(openBtnId);
  const closeBtn = document.getElementById(closeBtnId);
  const modal   = document.getElementById(modalId);
  if (!modal) return;
  openBtn?.addEventListener('click', () => { modal.style.display = 'flex'; });
  closeBtn?.addEventListener('click', () => { modal.style.display = 'none'; });
}

// -------------------------------
// Holiday Modal
// -------------------------------
document.addEventListener("DOMContentLoaded", () => {
  simpleModal("addHolidayBtn", "closeHolidayModal", "holidayModal");
});

// -------------------------------
// Add Lawyer Modal (Admin Only)
// -------------------------------
function initAddLawyerModal() {
  simpleModal("openAddLawyerModal", "closeAddLawyerModal", "addLawyerModal");
}

// -------------------------------
// Edit Lawyer Modal Logic
// -------------------------------
function initEditLawyerModal() {
  const modal    = document.getElementById("editLawyerModal");
  const closeBtn = document.getElementById("closeEditLawyerModal");

  const idField    = document.getElementById("editLawyerId");
  const nameField  = document.getElementById("editLawyerName");
  const emailField = document.getElementById("editLawyerEmail");
  const roleField  = document.getElementById("editLawyerRole");

  if (!modal || !closeBtn || !idField || !nameField || !emailField || !roleField) return;

  document.querySelectorAll(".editLawyerBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      idField.value = btn.dataset.id || "";
      nameField.value = btn.dataset.name || "";
      emailField.value = btn.dataset.email || "";
      roleField.value = btn.dataset.role || "lawyer";

      modal.style.display = "flex";
    });
  });

  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });
}

// -------------------------------
// Delete Routing Rule Modal Logic (safe)
// -------------------------------
function initDeleteRuleModal() {
  const modal = document.getElementById("deleteRuleModal");
  const closeBtn = document.getElementById("closeDeleteRuleModal");
  const idInput = document.getElementById("deleteRuleId");
  const label = document.getElementById("deleteRuleLabel");

  // If this page doesn't have routing rule modal, skip safely
  if (!modal || !closeBtn || !idInput || !label) return;

  document.querySelectorAll(".deleteRuleBtn").forEach(btn => {
    btn.addEventListener("click", () => {
      const id = btn.dataset.id || "";
      const type = btn.dataset.type || "";

      idInput.value = id;
      label.innerText = `Are you sure you want to delete the routing rule for: "${type}"?`;
      modal.style.display = "flex";
    });
  });

  closeBtn.addEventListener("click", () => {
    modal.style.display = "none";
  });

  // Optional: click outside closes
  modal.addEventListener("click", (e) => {
    if (e.target === modal) modal.style.display = "none";
  });
}

// -------------------------------
// Generic Confirm Action Modal (archive/restore/purge)
// -------------------------------
function initConfirmActionModal() {
  const modal = document.getElementById('confirmActionModal');
  if (!modal) return;

  const titleEl = document.getElementById('confirmActionTitle');
  const labelEl = document.getElementById('confirmActionLabel');

  const form = document.getElementById('confirmActionForm');
  const idInput = document.getElementById('confirmActionId');

  const confirmBtn = document.getElementById('confirmActionBtn');
  const closeBtn = document.getElementById('closeConfirmActionModal');

  if (!titleEl || !labelEl || !form || !idInput || !confirmBtn || !closeBtn) return;

  function openModal({ action, id, title, message, confirmText }) {
    form.action = action || '';
    idInput.value = id || '';

    titleEl.textContent = title || 'Confirm';
    labelEl.textContent = message || 'Are you sure?';
    confirmBtn.textContent = confirmText || 'Confirm';

    modal.style.display = 'flex';
  }

  function closeModal() {
    modal.style.display = 'none';
    form.action = '';
    idInput.value = '';
  }

  // Use event delegation for all .openConfirmModal buttons
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('.openConfirmModal');
    if (!btn) return;

    openModal({
      action: btn.dataset.action,
      id: btn.dataset.id,
      title: btn.dataset.title,
      message: btn.dataset.message,
      confirmText: btn.dataset.confirm
    });
  });

  closeBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.style.display !== 'none') closeModal();
  });
}

// -------------------------------
// Init all (single DOMContentLoaded)
// -------------------------------
document.addEventListener("DOMContentLoaded", () => {
  initAddLawyerModal();
  initEditLawyerModal();
  initDeleteRuleModal();
  initConfirmActionModal();
});

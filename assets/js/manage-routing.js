// assets/js/manage-routing.js
// Modal wiring and table search for manage_routing.php.

document.addEventListener('DOMContentLoaded', () => {

  // ── Add Rule Modal ──────────────────────────────────────
  document.getElementById('openAddRuleModal')?.addEventListener('click', () => {
    document.getElementById('addRuleModal').style.display = 'flex';
  });
  document.getElementById('closeAddRuleModal')?.addEventListener('click', () => {
    document.getElementById('addRuleModal').style.display = 'none';
  });

  // ── Edit Rule Modal ─────────────────────────────────────
  document.querySelectorAll('.editRuleBtn').forEach(btn => {
    btn.addEventListener('click', () => {
      document.getElementById('editRuleId').value      = btn.dataset.id;
      document.getElementById('editRuleDisplay').value = btn.dataset.display || '';
      document.getElementById('editRuleType').value    = btn.dataset.type;
      document.getElementById('editRuleLawyer').value  = btn.dataset.lawyer;
      document.getElementById('editRuleActive').value  = btn.dataset.active;

      const cc       = btn.dataset.cc ? btn.dataset.cc.split(',') : [];
      const ccSelect = document.getElementById('editRuleCC');
      [...ccSelect.options].forEach(opt => {
        opt.selected = cc.includes(opt.value);
      });

      document.getElementById('editRuleModal').style.display = 'flex';
    });
  });
  document.getElementById('closeEditRuleModal')?.addEventListener('click', () => {
    document.getElementById('editRuleModal').style.display = 'none';
  });

  // ── Table search ────────────────────────────────────────
  document.getElementById('routingSearch')?.addEventListener('keyup', function () {
    const term = this.value.toLowerCase();
    document.querySelectorAll('.data-table tbody tr').forEach(row => {
      const text = [row.cells[0], row.cells[1], row.cells[2]]
        .map(c => c?.innerText.toLowerCase() ?? '')
        .join(' ');
      row.style.display = text.includes(term) ? '' : 'none';
    });
  });

});

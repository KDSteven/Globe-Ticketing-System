(function(){
  const MODAL  = document.getElementById('dueSoonModal');
  const TBODY  = document.getElementById('dueSoonTbody');
  const SOUND  = document.getElementById('alertSound');
  const ACKBTN = document.getElementById('ackBtn');
  const SNOOZE = document.getElementById('snoozeBtn');

  let lastTicketIds = [];

  function snoozeActive(){
    const ts = localStorage.getItem('dueSoonSnoozeUntil');
    return ts && Date.now() < Number(ts);
  }

  function openModal(items){
    // Fill rows
    TBODY.innerHTML = items.map(it => `
      <tr>
        <td style="padding:8px;border-bottom:1px solid #f1f1f1;font-weight:700">${escapeHtml(it.ticket_code)}</td>
        <td style="padding:8px;border-bottom:1px solid #f1f1f1">${escapeHtml(it.full_name)}</td>
        <td style="padding:8px;border-bottom:1px solid #f1f1f1">${escapeHtml(it.contract_type)}</td>
        <td style="padding:8px;border-bottom:1px solid #f1f1f1">${escapeHtml(it.priority)}</td>
        <td style="padding:8px;border-bottom:1px solid #f1f1f1">${escapeHtml(it.due_text)}</td>
      </tr>
    `).join('');
    MODAL.style.display = 'block';
  }

  function closeModal(){ MODAL.style.display = 'none'; }

  async function fetchDueSoon(){
    if (snoozeActive()) return;

    try{
      const r = await fetch('/api/due_soon.php', {credentials:'same-origin'});
      if (!r.ok) return;
      const data = await r.json();
      const items = Array.isArray(data.items) ? data.items : [];

      if (items.length > 0){
        const ids = items.map(x=>x.id).join(',');
        if (ids !== lastTicketIds.join(',')){
          // new set -> play sound and show
          lastTicketIds = items.map(x=>x.id);
          try { SOUND.currentTime = 0; SOUND.play(); } catch(e){}
          openModal(items);
        }
      }
    }catch(e){ /* silent */ }
  }

  ACKBTN.addEventListener('click', async ()=>{
    const ids = lastTicketIds.slice();
    if (!ids.length){ closeModal(); return; }
    try{
      await fetch('/api/ack_alerts.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        credentials:'same-origin',
        body: JSON.stringify({ ticket_ids: ids, alert_type: 'pre_overdue_24h' })
      });
    }catch(e){}
    closeModal();
  });

  SNOOZE.addEventListener('click', ()=>{
    const until = Date.now() + 10*60*1000; // 10 minutes
    localStorage.setItem('dueSoonSnoozeUntil', String(until));
    closeModal();
  });

  // small HTML escaper
  function escapeHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, c => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[c]));
  }

  // Poll every 60 seconds; also check shortly after load
  setTimeout(fetchDueSoon, 1500);
  setInterval(fetchDueSoon, 60000);

  // Optional: also check when tab regains focus
  window.addEventListener('focus', fetchDueSoon);
})();
// -------- Bell dropdown (single source of truth) ----------
(() => {
  const bell = document.getElementById('notifBell');
  const dropdown = document.getElementById('notifDropdown');
  const listEl = document.getElementById('notifList');
  const badge = document.getElementById('notifBadge'); // may not exist; guard below

  if (!bell || !dropdown || !listEl) return; // fail-safe if markup is missing

  // Toggle dropdown
  bell.addEventListener('click', (e) => {
    dropdown.classList.toggle('show');
    e.stopPropagation();
  });
  document.addEventListener('click', () => dropdown.classList.remove('show'));

  // Load notifications from API
  async function load() {
    try {
      const res = await fetch('/api/notif_list.php', { credentials: 'same-origin' });
      if (!res.ok) {
        if (badge) badge.style.display = 'none';
        listEl.innerHTML = `<div class="notif-item">Sign in to see notifications.</div>`;
        return;
      }

      const json = await res.json();
      const items = Array.isArray(json.items) ? json.items : [];
      const count = Number(json.count || items.length || 0);

      // Badge (optional)
      if (badge) {
        if (count > 0) {
          badge.style.display = 'inline-flex';
          badge.textContent = String(count);
        } else {
          badge.style.display = 'none';
        }
      }

      // Empty state
      if (!items.length) {
        listEl.innerHTML = `<div class="notif-item">No overdue or due-soon tickets.</div>`;
        return;
      }

      // Render list (note: API returns "due_text", not "due_date")
      listEl.innerHTML = items.map(n => {
        const isOverdue = n.type === 'overdue';
        const icon = isOverdue
          ? '<i class="fa-solid fa-triangle-exclamation" style="color:#e53935"></i>'
          : '<i class="fa-solid fa-hourglass-half" style="color:#f4a000"></i>';
        const chip = isOverdue
          ? '<span style="background:#ffe3e1;color:#b11b16;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700">Overdue</span>'
          : '<span style="background:#ffe9bf;color:#8a6500;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700">Due in 24h</span>';

        return `
          <div class="notif-item" onclick="location.href='admin_dashboard.php?q=${encodeURIComponent(n.ticket_code)}'">
            <div style="width:22px;text-align:center;margin-right:10px">${icon}</div>
            <div>
              <div style="font-weight:700;color:#1f2352">${esc(n.ticket_code)} · ${esc(n.full_name)} ${chip}</div>
              <div style="color:#495057;font-size:.88rem">Due: ${esc(n.due_text)}</div>
            </div>
          </div>`;
      }).join('');
    } catch (e) {
      listEl.innerHTML = `<div class="notif-item">Failed to load notifications.</div>`;
    }
  }

  function esc(s){ return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

  load();
  setInterval(load, 60000); // refresh every minute
})();


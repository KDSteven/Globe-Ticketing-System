   // notification.js - Fixed version with DOM readiness and guards

   document.addEventListener('DOMContentLoaded', function() {
     console.log('notification.js: DOM loaded, initializing...');

     // Shared utility: HTML escaper
     function esc(s) {
       return String(s ?? '').replace(/[&<>"']/g, c => ({
         '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
       }[c]));
     }

     // ---------- DUE-SOON MODAL LOGIC (Conditional: Only if elements exist) ----------
     (function initDueSoonModal() {
       const MODAL = document.getElementById('dueSoonModal');
       const TBODY = document.getElementById('dueSoonTbody');
       const SOUND = document.getElementById('alertSound');
       const ACKBTN = document.getElementById('ackBtn');
       const SNOOZE = document.getElementById('snoozeBtn');

       if (!MODAL || !TBODY || !SOUND || !ACKBTN || !SNOOZE) {
         console.warn('notification.js: Due-soon modal elements not found. Skipping modal logic.');
         return; // Skip if modal isn't on this page
       }

         // ✅ FIX: Browser requires user interaction before allowing sound
        let soundAllowed = false;
        window.addEventListener("click", () => soundAllowed = true, { once: true });
        window.addEventListener("keydown", () => soundAllowed = true, { once: true });
        window.addEventListener("scroll", () => soundAllowed = true, { once: true });

       console.log('notification.js: Initializing due-soon modal.');
       let lastTicketIds = [];

       function snoozeActive() {
         const ts = localStorage.getItem('dueSoonSnoozeUntil');
         return ts && Date.now() < Number(ts);
       }

        function openModal(items) {
          TBODY.innerHTML = `
            <div class="ds-table">

              <div class="ds-table-header">
                <span>Ticket</span>
                <span>Requestor</span>
                <span>Contract Type</span>
                <span>Reviewer</span>
                <span>Due Date</span>
              </div>

              ${items.map(it => `
                <div class="ds-table-row">
                  <span class="ds-ticket-id">${esc(it.ticket_code)}</span>
                  <span>${esc(it.full_name)}</span>
                  <span class="ds-contract">${esc(it.contract_type)}</span>
                  <span class="ds-reviewer">
                    ${esc(it.reviewer_name || 'Unassigned')}
                  </span>
                  <span class="ds-due">${esc(it.due_text)}</span>
                </div>
              `).join('')}

            </div>
          `;

          MODAL.style.display = 'block';
        }
  
       function closeModal() { MODAL.style.display = 'none'; }

       async function fetchDueSoon() {
         if (snoozeActive()) return;
         try {
           const r = await fetch('/api/due_soon.php', { credentials: 'same-origin' });
           if (!r.ok) return;
           const data = await r.json();
           const items = Array.isArray(data.items) ? data.items : [];
           if (items.length > 0) {
             const ids = items.map(x => x.id).join(',');
             if (ids !== lastTicketIds.join(',')) {
               lastTicketIds = items.map(x => x.id);
               // 🔊 Play alert sound only if user interacted (Chrome autoplay fix)
                if (soundAllowed) {
                    SOUND.currentTime = 0;
                    SOUND.play().catch(err => {
                        console.warn("Sound blocked:", err);
                    });
                } else {
                    console.warn("Sound blocked until user interacts with the page.");
                }
               openModal(items);
             }
           }
         } catch (e) { console.error('notification.js: fetchDueSoon error', e); }
       }

       ACKBTN.addEventListener('click', async () => {
         const ids = lastTicketIds.slice();
         if (!ids.length) { closeModal(); return; }
         try {
           await fetch('/api/ack_alerts.php', {
             method: 'POST',
             headers: { 'Content-Type': 'application/json' },
             credentials: 'same-origin',
             body: JSON.stringify({ ticket_ids: ids, alert_type: 'pre_overdue_24h' })
           });
         } catch (e) { console.error('notification.js: Ack alerts error', e); }
         closeModal();
       });

       SNOOZE.addEventListener('click', () => {
         const until = Date.now() + 5 * 1000; // 5 seconds for testing
         localStorage.setItem('dueSoonSnoozeUntil', String(until));
         closeModal();
       });

       // Poll: Check shortly after load, then every 60 seconds
       setTimeout(fetchDueSoon, 1500);
       setInterval(fetchDueSoon, 60000);
       window.addEventListener('focus', fetchDueSoon);
     })();

     // ---------- BELL DROPDOWN LOGIC (Always attempt if elements exist) ----------
     (function initBellDropdown() {
       const bell = document.getElementById('notifBell');
       const dropdown = document.getElementById('notifDropdown');
       const listEl = document.getElementById('notifList');
       const badge = document.getElementById('notifBadge');

      

       if (!bell || !dropdown || !listEl) {
         console.error('notification.js: Bell dropdown elements not found. Notifications will not work.');
         return;
       }

       console.log('notification.js: Initializing bell dropdown.');

       // Toggle dropdown
       bell.addEventListener('click', (e) => {
         dropdown.classList.toggle('show');
         e.stopPropagation();
       });

       // Keep dropdown open when clicking inside
       dropdown.addEventListener('click', (e) => e.stopPropagation());

       // Close on outside click
       document.addEventListener('click', () => dropdown.classList.remove('show'));

       // Load notifications
       async function load() {
         try {
           const res = await fetch('/api/notif_list.php', { credentials: 'same-origin' });
           if (!res.ok) {
             console.warn('notification.js: Notif API failed', res.status);
             if (badge) badge.style.display = 'none';
             listEl.innerHTML = `<div class="notif-item">Sign in to see notifications.</div>`;
             return;
           }

           const json = await res.json();
           const items = Array.isArray(json.items) ? json.items : [];
           const count = Number(json.count || items.length || 0);

           // Update badge
           if (badge) {
             if (count > 0) {
               badge.style.display = 'inline-flex';
               badge.textContent = String(count);
             } else {
               badge.style.display = 'none';
             }
           }

           // Render list
           if (!items.length) {
             listEl.innerHTML = `<div class="notif-item">No overdue or due-soon tickets.</div>`;
             return;
           }

           listEl.innerHTML = items.map(n => {
             const isOverdue = n.type === 'overdue';
             const icon = isOverdue
               ? '<i class="fa-solid fa-triangle-exclamation" style="color:#e53935"></i>'
               : '<i class="fa-solid fa-hourglass-half" style="color:#f4a000"></i>';
             const chip = isOverdue
               ? '<span style="background:#ffe3e1;color:#b11b16;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700">Overdue</span>'
               : '<span style="background:#ffe9bf;color:#8a6500;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:700">Due in 24h</span>';

             return `
               <div class="notif-item" onclick="location.href='tickets.php?q=${encodeURIComponent(n.ticket_code)}'">
                 <div style="width:22px;text-align:center;margin-right:10px">${icon}</div>
                 <div>
                   <div style="font-weight:700;color:#1f2352">${esc(n.ticket_code)} · ${esc(n.full_name)} ${chip}</div>
                   <div style="color:#495057;font-size:.88rem">Due: ${esc(n.due_text)}</div>
                 </div>
               </div>`;
           }).join('');
         } catch (e) {
           console.error('notification.js: Load notifications error', e);
           listEl.innerHTML = `<div class="notif-item">Failed to load notifications.</div>`;
         }
       }

       // Initial load and poll
       load();
       setInterval(load, 60000);
     })();
   });
   
// assets/js/form-routing.js
(function () {
  // Contacts
  // const ROSELYN  = { name: "Roselyn Serrano",  email: "rgserrano@globe.com.ph" };
  // const ALEX     = { name: "Atty. Alex Austria",  email: "aaustria@globe.com.ph" };
  // const FRANCINE = { name: "Atty. Francine Turo", email: "fturo@globe.com.ph" };

  const ROSELYN  = { name: "Roselyn Serrano",  email: "kentperez30@gmail.com" };
  const ALEX     = { name: "Atty. Alex Austria",  email: "kentnotcant@gmail.com" };
  const FRANCINE = { name: "Atty. Francine Turo", email: "ksperez.degullado@gmail.com" };

  // Elements
  const groupSel   = document.getElementById("group");
  const tribeBlk   = document.getElementById("tribe-block");   // one single block, hidden by default
  const tribeSel   = document.getElementById("tribe");
  const lawyer     = document.getElementById("lawyer");
  const lawyerDisp = document.getElementById("lawyer_display");
  const cc         = document.getElementById("cc_emails");
  const ccDisp     = document.getElementById("cc_display");

  // Tribe maps
  const alexTribes = new Set([
    "Key Accounts - Hyperscaler","Key Accounts - Wholesale 2","Key Accounts - Conglo 2",
    "Strategic Verticals - FSI 1","Strategic Verticals - IT & BPM 1","Strategic Verticals - IT & BPM 3",
    "Strategic Verticals - Supply Chain 2","Strategic Verticals - Supply Chain 4",
    "Geo & OMNI - NCL","Geo & OMNI - NGMA","Geo & OMNI - SGMA 2","Geo & OMNI - VIS 2",
    "Geo & OMNI - OMNI","Partner Lifecycle Management (PLM)","GTIBH"
  ]);
  const francineTribes = new Set([
    "Key Accounts - Wholesale 1","Key Accounts - Conglo 1","Key Accounts - Conglo 3",
    "Strategic Verticals - FSI 2","Strategic Verticals - IT & BPM 2",
    "Strategic Verticals - Supply Chain 1","Strategic Verticals - Supply Chain 3",
    "Strategic Verticals - GEO VisMin","Geo & OMNI - SL","Geo & OMNI - SGMA 1",
    "Geo & OMNI - VIS 1","Geo & OMNI - MIN","Government"
  ]);

  // Helpers
  function setAssignee(primary, ccList = []) {
    const uniq = Array.from(new Set(ccList.filter(Boolean))); // de-dupe CCs
    lawyer.value     = primary ? `${primary.name} <${primary.email}>` : "";
    lawyerDisp.value = primary ? `${primary.name} (${primary.email})` : "";
    cc.value         = uniq.join(", ");
    ccDisp.value     = cc.value;
  }

  function onGroupChange() {
    const raw = groupSel.value; // e.g., "B2B" or "BB|ALEX"

    if (!raw) {
      tribeBlk.style.display = "none";
      tribeBlk.setAttribute("aria-expanded", "false");
      tribeSel.required = false;
      tribeSel.value = "";
      setAssignee(null, []);
      return;
    }

    // B2B -> show tribe selector & require it; CC Roselyn
    if (raw === "B2B") {
      tribeBlk.style.display = "";
      tribeBlk.setAttribute("aria-expanded", "true");
      tribeSel.required = true;

      if (!tribeSel.value) {
        setAssignee(null, [ROSELYN.email]);
      } else {
        onTribeChange();
      }
      return;
    }

    // Not B2B -> hide tribe selector and route by group
    tribeBlk.style.display = "none";
    tribeBlk.setAttribute("aria-expanded", "false");
    tribeSel.required = false;
    tribeSel.value = "";

    const [code, route] = raw.split("|");         // e.g., ["BB","ALEX"]
    const inCommercial = ["BB","EDS","PEDG","CMB","CMG","MKT","CCO"].includes(code);

    let primary = null;
    if (route === "ALEX") primary = ALEX;
    else if (route === "FRANCINE") primary = FRANCINE;

    // CC policy:
    //  - Commercial groups: always CC Roselyn
    //  - Other groups: CC the assigned lawyer by default so it's not blank
    const ccList = inCommercial ? [ROSELYN.email] : (primary ? [primary.email] : []);

    setAssignee(primary, ccList);
  }

  function onTribeChange() {
    if (groupSel.value !== "B2B") return;

    const t = tribeSel.value;
    const ccList = [ROSELYN.email]; // always CC Roselyn for B2B

    if (!t) { setAssignee(null, ccList); return; }
    if (alexTribes.has(t))      setAssignee(ALEX, ccList);
    else if (francineTribes.has(t)) setAssignee(FRANCINE, ccList);
    else setAssignee(null, ccList);
  }

  groupSel.addEventListener("change", onGroupChange);
  tribeSel.addEventListener("change", onTribeChange);

  // Initialize on load
  onGroupChange();
})();

// Show/hide "Other" input field dynamically
(function () {
  const otherRadio = document.getElementById("contract_other_radio");
  const otherInput = document.getElementById("contract_other");
  const radios = document.querySelectorAll("input[name='contract_type']");

  radios.forEach(radio => {
    radio.addEventListener("change", () => {
      if (otherRadio.checked) {
        otherInput.style.display = "block";
        otherInput.required = true;
        otherInput.focus();
      } else {
        otherInput.style.display = "none";
        otherInput.required = false;
        otherInput.value = "";
      }
    });
  });
})();

  (function () {
    const form = document.querySelector('form.card');
    const link = document.getElementById('doc_link');
    const files = document.getElementById('attachments');
    const hint = document.getElementById('attachHint');
    const MAX_FILES = 5;

    function updateCount() {
      const count = files.files ? files.files.length : 0;
      if (count > MAX_FILES) {
        alert(`You can upload up to ${MAX_FILES} files only.`);
        // Trim selection visually (not all browsers support programmatic trim)
      }
      if (hint) hint.textContent = `${Math.min(count, MAX_FILES)} of ${MAX_FILES} files selected.`;
    }

    files.addEventListener('change', updateCount);

    form.addEventListener('submit', function (e) {
      const hasLink = (link.value || '').trim();
      const isNA = hasLink.toLowerCase() === 'n/a';
      const fileCount = (files.files || []).length;

      // must have either a link (or N/A) OR at least 1 file
      if (!hasLink && fileCount === 0) {
        e.preventDefault();
        alert('Please provide a Google Docs/Sheets link (or type "N/A") OR attach at least one file.');
        link.focus();
        return;
      }
      // cap number of files
      if (fileCount > MAX_FILES) {
        e.preventDefault();
        alert(`Please select up to ${MAX_FILES} files only.`);
        return;
      }
    });
  })();

(function(){
  const prevRadios = document.getElementsByName('prev_reviewed');
  const prevWrap   = document.getElementById('prevTicketWrap');
  const btnLoad    = document.getElementById('loadPrevTicketBtn');
  const inputId    = document.getElementById('prev_ticket_id');
  const statusEl   = document.getElementById('prevTicketStatus');

  // Targets to fill
  const $ = (id) => document.getElementById(id);
  const setVal = (id, v) => { const el = $(id); if (el) el.value = v ?? ''; };

  const radioSet = (name, value) => {
    const list = document.querySelectorAll(`input[name="${name}"]`);
    let matched = false;
    list.forEach(r => {
      if (r.value === value) { r.checked = true; matched = true; }
      else if (!matched && value && value.toUpperCase() === 'OTHER' && r.value.toUpperCase() === 'OTHER') {
        r.checked = true; matched = true;
      }
    });
    return matched;
  };

  const showIf = (el, cond) => { if (el) el.style.display = cond ? '' : 'none'; };

  // Toggle Ticket field visibility
  function onPrevReviewedChange() {
    const val = Array.from(prevRadios).find(r => r.checked)?.value || 'No';
    showIf(prevWrap, val === 'Yes');
    statusEl.textContent = '';
  }
  prevRadios.forEach(r => r.addEventListener('change', onPrevReviewedChange));
  onPrevReviewedChange();

  // Helper: when "Other" radio is selected, show the text field
  function handleOtherToggles(data){
    // Contract type
    const isOtherContract = (data.contract_type || '').toUpperCase() === 'OTHER';
    const contractOtherInput = document.getElementById('contract_other');
    const contractOtherRadio = document.getElementById('contract_other_radio');
    if (contractOtherRadio) contractOtherRadio.checked = isOtherContract;
    if (contractOtherInput) {
      contractOtherInput.style.display = isOtherContract ? '' : 'none';
      contractOtherInput.value = isOtherContract ? (data.contract_other || '') : '';
    }

    // PD Nature
    const isOtherPd = (data.pd_nature || '').toUpperCase() === 'OTHER';
    const pdOtherRadio = document.getElementById('pd_other_radio');
    const pdOtherText  = document.getElementById('pd_other_text');
    if (pdOtherRadio) pdOtherRadio.checked = isOtherPd;
    if (pdOtherText) {
      pdOtherText.style.display = isOtherPd ? '' : 'none';
      pdOtherText.value = isOtherPd ? (data.pd_other_text || '') : '';
    }
  }

  // Helper: select dropdown by value (safe)
  function setSelectValue(id, value){
    const sel = $(id);
    if (!sel || value == null) return;
    const opts = Array.from(sel.options).map(o => o.value);
    if (opts.includes(value)) sel.value = value;
  }

  // If B2B group, show tribe block
  function syncTribeVisibility(groupVal){
    const tribeBlock = document.querySelector('#tribe-block');
    if (tribeBlock) {
      tribeBlock.style.display = (groupVal && groupVal.startsWith('B2B')) ? '' : 'none';
    }
  }

  // Fetch and fill
  btnLoad?.addEventListener('click', async () => {
    const tid = (inputId?.value || '').trim();
    statusEl.textContent = '';
    if (!tid) {
      statusEl.textContent = 'Please enter a Ticket ID.';
      return;
    }
    try {
      statusEl.textContent = 'Loading previous ticket…';
      const res = await fetch(`api/fetch_ticket_public.php?ticket_id=${encodeURIComponent(tid)}`, {
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json' }
      });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const payload = await res.json();

      if (!payload || !payload.success) {
        statusEl.textContent = payload?.message || 'Ticket not found or you do not have access.';
        return;
      }

      const d = payload.data || {};

      // Text inputs
      setVal('full_name', d.full_name);
      setVal('email', d.email);

      // Group + Tribe
      setSelectValue('group', d.group);
      syncTribeVisibility(d.group);
      setSelectValue('tribe', d.tribe);

      // Assigned lawyer & CC (display + hidden)
      setVal('lawyer_display', d.assigned_lawyer);
      setVal('cc_display', d.cc_emails);
      setVal('lawyer', d.assigned_lawyer);
      setVal('cc_emails', d.cc_emails);

      // Contract basics
      setVal('summary', d.summary);
      radioSet('contract_type', d.contract_type);
      handleOtherToggles(d);
      setVal('contract_other', d.contract_other);

      // Parties
      setVal('customer', d.customer);
      setVal('vendor', d.vendor);

      // Personal Data nature
      radioSet('pd_nature', d.pd_nature);
      setVal('pd_other_text', d.pd_other_text);

      // Part 3
      setVal('clauses', d.clauses);
      setVal('doc_link', d.doc_link);

      statusEl.textContent = 'Previous ticket loaded. Review pre-filled details before submitting.';
    } catch (e) {
      console.error(e);
      statusEl.textContent = 'Error loading ticket. Please try again or contact support.';
    }
  });

  // OPTIONAL: live “Other” toggles if user changes them manually
  document.getElementById('contractTypeGroup')?.addEventListener('change', (e) => {
    const isOther = (e.target?.value || '').toUpperCase() === 'OTHER';
    const inp = document.getElementById('contract_other');
    if (inp) inp.style.display = isOther ? '' : 'none';
  });
  document.getElementById('pdNatureGroup')?.addEventListener('change', (e) => {
    const isOther = (e.target?.value || '').toUpperCase() === 'OTHER';
    const inp = document.getElementById('pd_other_text');
    if (inp) inp.style.display = isOther ? '' : 'none';
  });
})();

btnLoad?.addEventListener('click', async () => {
  const tid = (inputId?.value || '').trim();
  statusEl.textContent = '';
  if (!tid) {
    statusEl.textContent = 'Please enter a Ticket ID.';
    return;
  }
  try {
    statusEl.textContent = 'Loading previous ticket…';
    const res = await fetch('api/fetch_ticket_public.php?ticket_id=' + encodeURIComponent(tid), {
      credentials: 'same-origin',
      headers: { 'Accept': 'application/json' }
    });

    const raw = await res.text(); // read raw first for better error messages
    let payload = null;
    try { payload = JSON.parse(raw); } catch (e) { /* not JSON */ }

    if (!res.ok) {
      statusEl.textContent = `Server error (${res.status}). ${raw?.slice(0,200) || ''}`;
      return;
    }
    if (!payload || !payload.success) {
      statusEl.textContent = (payload && payload.message) ? payload.message : 'Ticket not found or endpoint error.';
      return;
    }

    const d = payload.data || {};
    // ... (keep your existing prefill code here)
    statusEl.textContent = 'Previous ticket loaded. Review pre-filled details before submitting.';
  } catch (e) {
    console.error(e);
    statusEl.textContent = 'Network error loading ticket. Check Apache/PHP error log.';
  }
});



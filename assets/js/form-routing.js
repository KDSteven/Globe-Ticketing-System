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




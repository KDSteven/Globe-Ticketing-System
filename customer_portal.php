<?php
// index.php — Requestor Submission Portal
// Handles flash messages from submit.php

$ok    = isset($_GET['ok']);
$error = isset($_GET['error']) ? urldecode($_GET['error']) : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Data Agreements & Contracts – Request Form</title>
  <link rel="stylesheet" href="assets/css/admin.css">
  <link rel="stylesheet" href="assets/css/form.css">
  <link rel="stylesheet" href="assets/css/wizard.css">
  <link rel="stylesheet" href="/assets/css/toast.css">
  <link rel="icon" type="image/x-icon" href="/assets/img/favicon/favicon.ico">
</head>
<body>

<?php
$brand = [
  'showMenuToggle' => false, // hide ☰
  'showNotif'      => false, // hide bell
];
include __DIR__ . '/assets/partials/brandbar.php';
?>


  <main class="container page">
<?php if ($ok || $error): ?>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      <?php if ($ok): ?>
        toast.success('Request Submitted!', 'Your request was submitted successfully and will be reviewed shortly.');
      <?php elseif ($error): ?>
        toast.error('Submission Failed', <?= json_encode($error) ?>);
      <?php endif; ?>
    });
  </script>
<?php endif; ?>

  <!-- Progress bar -->
  <nav class="wizard-progress" aria-label="Form progress">
    <ol class="wizard-steps" id="wizardSteps">
      <li class="wizard-step is-active" data-step="1">
        <span class="step-index">1</span>
        <span class="step-label">Group & Contact</span>
      </li>
      <li class="wizard-step" data-step="2">
        <span class="step-index">2</span>
        <span class="step-label">Contract Nature</span>
      </li>
      <li class="wizard-step" data-step="3">
        <span class="step-index">3</span>
        <span class="step-label">Details & Files</span>
      </li>
    </ol>
  </nav>

  <form action="api/submit.php" method="post" class="card wizard-form" novalidate enctype="multipart/form-data" id="requestForm">
    <!-- STEP 1 -->
    <section class="wizard-pane" data-step="1" aria-labelledby="step1Title">
      <section class="notice-section card">
        <h2 class="section-title" id="step1Title">PART 1: ASSIGNED GROUP SPOCS.</h2>
        <p class="note"><strong>NOTE:</strong> COMMERCIAL AGREEMENTS NEED EDS REVIEW AND APPROVAL.</p>
        <p class="description">
          If you are part of <strong>B2B, EDS, CCO, PEDG, CMB, CMG, Marketing, or Broadband</strong>, kindly loop in 
          <strong>Roselyn Serrano</strong> (<a href="mailto:rgserrano@globe.com.ph">rgserrano@globe.com.ph</a>)
          in all contract reviews.
        </p>
      </section>

      <!-- 1.0 Preview Contract -->
      <div class="field">
        <label class="block">Has this contract been reviewed before?</label>
        <label class="radio">
          <input type="radio" name="prev_reviewed" value="Yes">
          <span>Yes</span>
        </label>
        <label class="radio">
          <input type="radio" name="prev_reviewed" value="No" checked>
          <span>No</span>
        </label>
      </div>

      <div id="prevTicketWrap" class="field" style="display:none;">
        <label for="prev_ticket_id">If Yes, please enter the previous Ticket ID</label>
        <div class="control">
          <input type="text" id="prev_ticket_id" name="prev_ticket_id" class="big-input" placeholder="e.g., GDA-093024">
        </div>
        <button type="button" class="btn" id="loadPrevTicketBtn">Load Previous Details</button>
        <small class="hint">We’ll fetch the previous request and auto-fill the form. You can still edit any field.</small>
        <div id="prevTicketStatus" class="hint" style="margin-top:6px;"></div>
      </div>

      <!-- 1.1 Full Name -->
    <div class="form-grid-2">
      <div class="field">
        <label for="full_name">1.1 Full Name</label>
        <input type="text" id="full_name" name="full_name" class="big-input" placeholder="Enter your full name" required>
        <small class="hint">Please enter your complete name.</small>
      </div>

      <!-- 1.2 Email Address -->
    
      <div class="field">
        <label for="email">1.2 Email Address</label>
        <input type="email" id="email" name="email" class="big-input" placeholder="Enter your email"
               pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
               title="Please enter a valid email address (e.g. name@example.com)" required>
        <small class="hint">We'll use this email to contact you regarding your request.</small>
      </div>
    </div>

      <div class="field">
        <label>1.3 GROUP: Select Your Group</label>
        <select id="group" name="group" required>
          <option value="" disabled selected>Select your group...</option>
        </select>
      </div>

      <div class="field" id="tribe-block" style="display:none">
        <label>B2B Tribe/Squad</label>
        <select id="tribe" name="tribe">
          <option value="" disabled selected>Select your Tribe/Squad...</option>
        </select>
      </div>


      <!-- Auto-filled targets -->
      <div class="field">
        <label>Assigned Lawyer</label>
        <input type="text" id="lawyer_display" class="big-input" readonly>
      </div>
      <div class="field">
        <label>Loop/CC Emails</label>
        <input type="text" id="cc_display" class="big-input" readonly>
      </div>
      <input type="hidden" name="assigned_lawyer" id="lawyer">
      <input type="hidden" name="cc_emails" id="cc_emails">
    </section>

    <!-- STEP 2 -->
    <section class="wizard-pane" data-step="2" aria-labelledby="step2Title" hidden>
      <section class="notice-section card" id="part2-header">
        <h2 class="section-title" id="step2Title">PART 2: NATURE OF THE CONTRACT/AGREEMENT.</h2>
        <p class="description"><em>To expedite the contract review, kindly answer the following questions below.</em></p>
      </section>

      <div class="field">
        <label for="summary">2.1 BRIEF SUMMARY</label>
        <textarea id="summary" name="summary" class="big-input" placeholder="What’s this contract about? Who provides/avails which service?" required></textarea>
      </div>

      <div class="field">
        <label>2.2 Contract Type</label>
        <p class="hint" style="margin-top:2px;">Select “Other” and type in the contract if it is not among the choices.</p>
        <fieldset class="radio-group" id="contractTypeGroup">
          <label class="radio"><input type="radio" name="contract_type" value="Data Processing Agreement (DPA)" required><span>DPA</span></label>
          <label class="radio"><input type="radio" name="contract_type" value="Data Sharing Agreement (DSA)"><span>DSA</span></label>
          <label class="radio"><input type="radio" name="contract_type" value="Non-Disclosure Agreement (NDA)"><span>NDA</span></label>
          <label class="radio"><input type="radio" name="contract_type" value="OTHER" id="contract_other_radio"><span>Other:</span></label>
          <input type="text" id="contract_other" name="contract_other" class="text-inline" placeholder="Type the contract name...">
        </fieldset>
      </div>

      <div class="field">
        <label for="customer">2.3 Who is the CUSTOMER in the contract?</label>
        <input type="text" id="customer" name="customer" class="big-input" placeholder="Your answer" required>
      </div>

      <div class="field">
        <label for="vendor">2.4 Who is the VENDOR in the contract?</label>
        <input type="text" id="vendor" name="vendor" class="big-input" placeholder="Your answer" required>
      </div>

      <div class="field">
        <label for="pd_nature">2.5 Nature of the Personal Data Transaction</label>
        <p class="hint">Choose the best description. If not listed, select “Others” and describe in the next field.</p>
        <fieldset class="choice-grid" id="pdNatureGroup" aria-required="true">
          <label class="radio-card">
            <input type="radio" name="pd_nature" value="Globe processes partner data" required>
            <div class="card-body">
              <img src="assets/img/Option 1.png" alt="Globe processes client/vendor/partner personal data" class="thumb">
              <div class="caption">Globe will process client/vendor/partner’s personal data.</div>
            </div>
          </label>

          <label class="radio-card">
            <input type="radio" name="pd_nature" value="Partner processes Globe data">
            <div class="card-body">
              <img src="assets/img/Option 2.png" alt="Client/vendor/partner processes Globe personal data" class="thumb">
              <div class="caption">Client/vendor/partner will process Globe’s personal data.</div>
            </div>
          </label>

          <label class="radio-card">
            <input type="radio" name="pd_nature" value="Both parties share/process data">
            <div class="card-body">
              <img src="assets/img/Option 3.png" alt="Both parties share and process the other’s personal data" class="thumb">
              <div class="caption">Both parties will share and process the other’s personal data.</div>
            </div>
          </label>

          <label class="radio-card">
            <input type="radio" name="pd_nature" value="OTHER" id="pd_other_radio">
            <div class="card-body">
              <img src="assets/img/Option 4.png" alt="Other kind of contract review" class="thumb">
              <div class="caption">Others</div>
            </div>
          </label>
        </fieldset>

        <input type="text" id="pd_other_text" name="pd_other_text" class="text-inline"
               placeholder="Describe the nature (e.g., NDA, main contract, etc.)" style="display:none;">
      </div>
    </section>

    <!-- STEP 3 -->
    <section class="wizard-pane" data-step="3" aria-labelledby="step3Title" hidden>
      <section class="notice-section card" id="part3-header">
        <h2 class="section-title" id="step3Title">PART 3: CONTRACT DETAILS</h2>
        <p class="description"><em>Provide references and files for review.</em></p>
      </section>

      <div class="field">
        <label for="clauses">3.1 Which SPECIFIC CLAUSES need to be reviewed?</label>
        <input type="text" id="clauses" name="clauses" class="big-input" placeholder="Your answer" required>
      </div>

      <div class="field">
        <label for="doc_link">3.2 Google Docs/Sheets Link (or N/A)</label>
        <p class="hint">Paste the link. If uploading a file, type <strong>N/A</strong> and upload below.</p>
        <input type="text" id="doc_link" name="doc_link" class="big-input" placeholder="https://docs.google.com/...  (or type N/A)" required>
      </div>

      <div class="field">
        <label for="attachments">3.3 Attach Documents</label>
        <p class="hint">
          (1) Contract to be reviewed and (2) Main contract, if any. You may upload up to <strong>5 files</strong>. Supported: PDF, DOC, DOCX, XLS, XLSX, CSV, images. Max 1 GB/file.
        </p>
        <input
          type="file"
          id="attachments"
          name="attachments[]"
          class="file-input"
          multiple
          accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
        />
        <small class="hint" id="attachHint">0 of 5 files selected.</small>
      </div>

      <!-- SLA (kept visible in final step) -->
      <section class="notice-section card" id="part4-sla">
        <h2 class="section-title">SERVICE LEVEL AGREEMENT (SLA)</h2>
        <p><strong>SLA.</strong> To manage expectations, please refer to our SLA for the following contracts below:</p>
        <ul>
          <li>For <strong>Globe</strong> contracts, NDAs, DPAs, DSAs and DP clauses: <br><strong>THREE (3)</strong> business days from time of request</li>
          <li>For <strong>Globe Group</strong> and <strong>third party/vendor</strong> contracts, NDAs, DPAs, DSAs, and DP clauses: <br><strong>FIVE (5)</strong> business days from time of request</li>
        </ul>
        <p class="notice">
          <strong>NOTICE:</strong> Effective <strong>September 1, 2025</strong>, all contract review assigned to <strong>Atty. Alex</strong> will be temporarily reassigned to <strong>Atty. Franz</strong>.
        </p>
      </section>
    </section>

    <!-- Wizard actions -->
    <div class="wizard-actions">
      <button type="button" class="btn" id="prevBtn" disabled>Back</button>
      <button type="button" class="btn primary" id="nextBtn">Next</button>
      <button type="submit" class="btn primary" id="submitBtn" hidden>Submit Request</button>
      <button type="reset" class="btn ghost" id="resetBtn">Clear</button>
    </div>
  </form>
</main>
  <script src="/assets/js/form-routing.js"></script>
  <script src="/assets/js/wizard.js"></script>
  <script src="/assets/js/prev_ticket_loader.js"></script>
<script>
let routingData = {};
let dropdownData = {};

fetch("/api/get_routing_list.php")
    .then(r => r.json())
    .then(data => {
        routingData  = data.routing;
        dropdownData = data.dropdown;

        populateGroupDropdown();
    });

/* ----------------------------------------
   1. POPULATE MAIN GROUP DROPDOWN
---------------------------------------- */
function populateGroupDropdown() {
    const groupSel = document.getElementById("group");
    groupSel.innerHTML = "";

    // ADD PLACEHOLDER HERE
    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = "Select your group...";
    placeholder.disabled = true;
    placeholder.selected = true;
    groupSel.appendChild(placeholder);

    Object.entries(dropdownData).forEach(([sectionLabel, groupList]) => {
        const optGroup = document.createElement("optgroup");
        optGroup.label = sectionLabel;

        groupList.forEach(groupName => {
            const opt = document.createElement("option");
            opt.value = groupName === "B2B" ? "B2B" : groupName;
            opt.textContent = groupName;
            optGroup.appendChild(opt);
        });

        groupSel.appendChild(optGroup);
    });
}

/* ----------------------------------------
   2. WHEN MAIN GROUP CHANGES
---------------------------------------- */
const groupSel = document.getElementById("group");
const tribeBlock = document.getElementById("tribe-block");
const tribeSel   = document.getElementById("tribe");

groupSel.addEventListener("change", () => {
    const selected = groupSel.value;

    // Reset lawyer + CC fields
    document.getElementById("lawyer_display").value = "";
    document.getElementById("lawyer").value = "";
    document.getElementById("cc_display").value = "";
    document.getElementById("cc_emails").value = "";

    // If B2B → show tribe list
    if (selected === "B2B") {
        showB2BTribes();
        return;
    }

    // If NOT B2B → hide tribes & auto-fill routing
    tribeBlock.style.display = "none";

    const rule = routingData[selected];
    if (!rule) return;

    document.getElementById("lawyer_display").value = rule.lawyer;
    document.getElementById("lawyer").value = rule.email;
    document.getElementById("cc_display").value = rule.cc.join(", ");
    document.getElementById("cc_emails").value = rule.cc.join(",");
});

/* ----------------------------------------
   3. POPULATE B2B TRIBE DROPDOWN
---------------------------------------- */
function showB2BTribes() {
    tribeBlock.style.display = "block";
    tribeSel.innerHTML = "";

    // Get all routing keys that belong to B2B squads
    const b2bTribes = Object.keys(routingData).filter(key =>
        key.includes("Key Accounts") ||
        key.includes("Strategic Verticals") ||
        key.includes("Geo & OMNI") ||
        key.includes("Government") ||
        key.includes("PLM") ||
        key.includes("GTIBH")
    );

    // Sort alphabetically to look clean
    b2bTribes.sort();
    // ADD PLACEHOLDER BACK
    const placeholder = document.createElement("option");
    placeholder.value = "";
    placeholder.textContent = "Select your Tribe/Squad...";
    placeholder.disabled = true;
    placeholder.selected = true;
    tribeSel.appendChild(placeholder);

    b2bTribes.forEach(t => {
        const opt = document.createElement("option");
        opt.value = t;
        opt.textContent = t;
        tribeSel.appendChild(opt);
    });
}

/* ----------------------------------------
   4. WHEN TRIBE IS SELECTED
---------------------------------------- */
tribeSel.addEventListener("change", () => {
    const tribeName = tribeSel.value;
    const rule = routingData[tribeName];
    if (!rule) return;

    document.getElementById("lawyer_display").value = rule.lawyer;
    document.getElementById("lawyer").value = rule.email;
    document.getElementById("cc_display").value = rule.cc.join(", ");
    document.getElementById("cc_emails").value = rule.cc.join(",");
});
</script>

</body>
</html>

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
  <link rel="stylesheet" href="assets/css/form.css">
</head>
<body>
  <header class="brandbar">
    <div class="brandbar__inner container">
      <div class="brandbar__logo" aria-hidden="true">
        <!-- Simple inline SVG “data” logo to match your mockup -->
        <svg viewBox="0 0 64 64" role="img" aria-label="Logo">
          <circle cx="32" cy="32" r="30" fill="#fff"></circle>
          <circle cx="20" cy="24" r="5" fill="#2E3192"></circle>
          <circle cx="44" cy="24" r="5" fill="#2E3192"></circle>
          <rect x="18" y="38" width="28" height="4" rx="2" fill="#2E3192"></rect>
        </svg>
      </div>
      <div class="brandbar__title">
        <div>Data Agreements and</div>
        <div>Contracts Review Request Form</div>
      </div>
    </div>
  </header>

  <main class="container page">
    <?php if ($ok): ?>
      <div class="alert success" role="alert">Your request was submitted. We’ll review it shortly.</div>
    <?php elseif ($error): ?>
      <div class="alert danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

        <form action="api/submit.php" method="post" class="card" novalidate enctype="multipart/form-data">
            <section class="notice-section card">
              <h2 class="section-title">PART 1: ASSIGNED GROUP SPOCS.</h2>
              <p class="note"><strong>NOTE:</strong> COMMERCIAL AGREEMENTS NEED EDS REVIEW AND APPROVAL.</p>
              <p class="description">
                If you are part of <strong>B2B, EDS, CCO, PEDG, CMB, CMG, Marketing, or Broadband</strong>, kindly loop in 
                <strong>Roselyn Serrano</strong> (<a href="mailto:rgserrano@globe.com.ph">rgserrano@globe.com.ph</a>)
                in all contract reviews.
              </p>
          </section>
          
        <!-- Full Name -->
        <div class="field">
            <label for="full_name">1.1 Full Name</label>
            <input type="text" id="full_name" name="full_name" class="big-input" placeholder="Enter your full name" required>
            <small class="hint">Please enter your complete name.</small>
        </div>

        <!-- Email Address -->
        <div class="field">
            <label for="email">1.2 Email Address</label>
            <input type="email" id="email" name="email" class="big-input" placeholder="Enter your email"  
            pattern="[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$"
            title="Please enter a valid email address (e.g. name@example.com)" required>
            <small class="hint">We'll use this email to contact you regarding your request.</small>
        </div>

        <!-- GROUP ROUTING -->
        <div class="field">
          <label for="group">1.3 GROUP: Select Your Group</label>
          <div class="control">
            <select id="group" name="group" required>
              <option value="">Select your group…</option>

              <!-- COMMERCIAL GROUPS (Always CC Roselyn) -->
              <optgroup label="Commercial Groups (Always CC: Roselyn Serrano)">
                <option value="B2B">B2B (Go to next question for Tribes & Squads)</option>

                <!-- Alex column -->
                <option value="BB|ALEX">Broadband Business (BB)</option>
                <option value="EDS|ALEX">Enterprise Data and Strategic Services (EDS)</option>
                <option value="PEDG|ALEX">Product Engineering and Digital Growth (PEDG)</option>

                <!-- Francine column -->
                <option value="CMB|FRANCINE">Consumer Mobile Business (CMB)</option>
                <option value="CMG|FRANCINE">Channel Management (CMG)</option>
                <option value="MKT|FRANCINE">Marketing (MKT)</option>
                <option value="CCO|FRANCINE">Office of the Chief Commercial Officer (CCO)</option>
              </optgroup>

              <!-- OTHER GROUPS -->
              <optgroup label="Other Groups">
                <!-- Alex column -->
                <option value="NTG|ALEX">Network Technical Group (NTG)</option>
                <option value="ISG|ALEX">Information Services Group (ISG)</option>
                <option value="CLSG|ALEX">Corporate & Legal Services Group (CLSG)</option>
                <option value="CorpComm|ALEX">Corporate Communications (CorpComm)</option>
                <option value="ISDP|ALEX">Information Security & Data Privacy (ISDP)</option>
                <option value="ICG|ALEX">Internal Controls (ICG)</option>

                <!-- Francine column -->
                <option value="STT|FRANCINE">ST Telemedia (STT)</option>
                <option value="OSMCX|FRANCINE">Office of Strategy Management & Customer Experience (OSMCX)</option>
                <option value="FBA|FRANCINE">Finance & Administration (FBA)</option>
                <option value="HR|FRANCINE">Human Resources (HR)</option>
              </optgroup>
            </select>
          </div>
          <small class="hint">Pick your group. If you choose B2B, you’ll then pick a Tribe/Squad.</small>
        </div>

        <!-- Tribe/Squad: only shown for B2B -->
<div class="field" id="tribe-block" style="display:none">
  <label for="tribe">B2B Tribe/Squad</label>
  <div class="control">
    <select id="tribe" name="tribe" class="select">
      <option value="">Select your Tribe/Squad…</option>

      <optgroup label="Atty. Alex Austria & Roselyn Serrano">
        <option>Key Accounts - Hyperscaler</option>
        <option>Key Accounts - Wholesale 2</option>
        <option>Key Accounts - Conglo 2</option>
        <option>Strategic Verticals - FSI 1</option>
        <option>Strategic Verticals - IT & BPM 1</option>
        <option>Strategic Verticals - IT & BPM 3</option>
        <option>Strategic Verticals - Supply Chain 2</option>
        <option>Strategic Verticals - Supply Chain 4</option>
        <option>Geo & OMNI - NCL</option>
        <option>Geo & OMNI - NGMA</option>
        <option>Geo & OMNI - SGMA 2</option>
        <option>Geo & OMNI - VIS 2</option>
        <option>Geo & OMNI - OMNI</option>
        <option>Partner Lifecycle Management (PLM)</option>
        <option>GTIBH</option>
      </optgroup>

      <optgroup label="Atty. Francine Turo & Roselyn Serrano">
        <option>Key Accounts - Wholesale 1</option>
        <option>Key Accounts - Conglo 1</option>
        <option>Key Accounts - Conglo 3</option>
        <option>Strategic Verticals - FSI 2</option>
        <option>Strategic Verticals - IT & BPM 2</option>
        <option>Strategic Verticals - Supply Chain 1</option>
        <option>Strategic Verticals - Supply Chain 3</option>
        <option>Strategic Verticals - GEO VisMin</option>
        <option>Geo & OMNI - SL</option>
        <option>Geo & OMNI - SGMA 1</option>
        <option>Geo & OMNI - VIS 1</option>
        <option>Geo & OMNI - MIN</option>
        <option>Government</option>
      </optgroup>
    </select>
  </div>
  <small class="hint">Choosing a Tribe/Squad auto-assigns the lawyer and CC emails.</small>
</div>

        <!-- Keep your existing Tribe/Squad block -->
        <div class="field" id="tribe-block" style="display:none">
          <label for="tribe">B2B Tribe/Squad</label>
          <div class="control">
            <select id="tribe" name="tribe" class="select">
              <option value="">Select your Tribe/Squad…</option>
              <!-- … your existing optgroups and options … -->
            </select>
          </div>
          <small class="hint">Choosing a Tribe/Squad auto-assigns the lawyer and CC emails.</small>
        </div>

        <!-- Auto-filled targets (same as before)
        <div class="field">
          <label>Assigned Lawyer</label>
          <input type="text" id="lawyer_display" class="big-input" readonly>
        </div>
        <div class="field">
          <label>Loop/CC Emails</label>
          <input type="text" id="cc_display" class="big-input" readonly>
        </div>

        Hidden fields actually posted
        <input type="hidden" name="assigned_lawyer" id="lawyer">
        <input type="hidden" name="cc_emails" id="cc_emails"> -->


        <!-- Auto-filled targets -->
        <div class="field">
          <label>Assigned Lawyer</label>
          <input type="text" id="lawyer_display" class="big-input" readonly>
        </div>
        <div class="field">
          <label>Loop/CC Emails</label>
          <input type="text" id="cc_display" class="big-input" readonly>
        </div>

        <!-- Hidden fields actually posted -->
        <input type="hidden" name="assigned_lawyer" id="lawyer">
        <input type="hidden" name="cc_emails" id="cc_emails">

          <section class="notice-section card" id="part2-header">
            <h2 class="section-title">PART 2: NATURE OF THE CONTRACT/AGREEMENT.</h2>
            <p class="description"><em>To expedite the contract review, kindly answer the following questions below.</em></p>
          </section>
        
        <!-- 2.1 Brief Summary -->
          <label for="summary">2.1 BRIEF SUMMARY: Please indicate a brief summary of what the contract is about. What kind of service is Globe providing? What kind of service is Globe availing of?</span></label>
          <textarea id="summary" name="summary" class="big-input" placeholder="Your answer" required></textarea>

        <!-- 2.2 Contract type (with “Other”) -->
          <label>2.2 Please select which kind of contract you are asking to be reviewed.<span style="color:#d00"></span></label>
          <p class="hint" style="margin-top:2px;">Select “Other” and type in the contract if it is not among the choices.</p>

          <fieldset class="radio-group" id="contractTypeGroup">
            <label class="radio"><input type="radio" name="contract_type" value="Data Processing Agreement (DPA)" required><span>Data Processing Agreement (DPA)</span></label>
            <label class="radio"><input type="radio" name="contract_type" value="Data Sharing Agreement (DSA)"><span>Data Sharing Agreement (DSA)</span></label>
            <label class="radio"><input type="radio" name="contract_type" value="Non-Disclosure Agreement (NDA)"><span>Non-Disclosure Agreement (NDA)</span></label>
            <label class="radio"><input type="radio" name="contract_type" value="OTHER" id="contract_other_radio"><span>Other:</span></label>
            <input type="text" id="contract_other" name="contract_other" class="text-inline" placeholder="Type the contract name...">
          </fieldset>
        </div>

        <!-- 2.3 Customer -->
          <label for="customer">2.3 Who is the CUSTOMER in the contract?</label>
          <input type="text" id="customer" name="customer" class="big-input" placeholder="Your answer" required>

        <!-- 2.4 Vendor -->
          <label for="vendor">2.4 Who is the VENDOR in the contract?</label>
          <input type="text" id="vendor" name="vendor" class="big-input" placeholder="Your answer" required>

            <div class="field">
              <label for="pd_nature">2.5 Nature of the Personal Data Transaction</label>
              <p class="hint">Please choose below which best describes the contract. If the choice is not listed, please click “Others” and comment the nature of the contract in the next question.</p>

              <fieldset class="choice-grid" id="pdNatureGroup" aria-required="true">
                <!-- Option A: Globe is Processor -->
                <label class="radio-card">
                  <input type="radio" name="pd_nature" value="Globe processes partner data" required>
                  <div class="card-body">
                    <img src="assets/img/Option 1.png" alt="Globe processes client/vendor/partner personal data" class="thumb">
                    <div class="caption">
                      Globe will process client/vendor/partner’s personal data.
                    </div>
                  </div>
                </label>

                <!-- Option B: Partner is Processor -->
                <label class="radio-card">
                  <input type="radio" name="pd_nature" value="Partner processes Globe data">
                  <div class="card-body">
                    <img src="assets/img/Option 2.png" alt="Client/vendor/partner processes Globe personal data" class="thumb">
                    <div class="caption">
                      Client/vendor/partner will process Globe’s personal data.
                    </div>
                  </div>
                </label>

                <!-- Option C: Data Sharing (both share & process) -->
                <label class="radio-card">
                  <input type="radio" name="pd_nature" value="Both parties share/process data">
                  <div class="card-body">
                    <img src="assets/img/Option 3.png" alt="Both parties share and process the other’s personal data" class="thumb">
                    <div class="caption">
                      Both parties will share and process the other’s personal data.
                    </div>
                  </div>
                </label>
                
                <!-- Option D: Others -->
                <label class="radio-card">
                  <input type="radio" name="pd_nature" value="OTHER" id="pd_other_radio">
                  <div class="card-body">
                    <img src="assets/img/Option 4.png" alt="Other kind of contract review" class="thumb">
                    <div class="caption">
                      Others
                    </div>
                  </div>
                </label>
              </fieldset>
            <!-- Part 3: Contract Details -->
          <section class="notice-section card" id="part2-header">
            <h2 class="section-title">PART 3: NATURE OF THE CONTRACT/AGREEMENT.</h2>
            <p class="description"><em>To expedite the contract review, kindly answer the following questions below.</em></p>
          </section>

          <!-- 3.1 Customer -->
          <label for="clauses">3.1 Which SPECIFIC CLAUSES of the contract needed to be reviewed</label>
          <input type="text" id="clauses" name="clauses" class="big-input" placeholder="Your answer" required>

              <!-- Appears only when “Others” is selected -->
              <input type="text" id="pd_other_text" name="pd_other_text" class="text-inline"
                    placeholder="Describe the nature (e.g., NDA, main contract, etc.)"
                    style="display:none;">
          
          <label for="doc_link">3.2 Please LINK the Google docs/sheets if any.</label>
            <p class="hint">
              Paste the link to the GDocs/Sheets below. If you will be uploading a file, kindly indicate
              <strong>"N/A"</strong> and upload the file in the next question.
            </p>
            <input type="text" id="doc_link" name="doc_link" class="big-input"
                  placeholder="https://docs.google.com/...  (or type N/A)"
                  required>

              <label for="attachments">3.3 If a link is not available, please ATTACH the following documents:</label>
                <p class="hint">
                  (1) Contract to be reviewed and (2) Main contract, if any. &nbsp;You may upload up to
                  <strong>5 files</strong>. Supported types: PDF, DOC, DOCX, XLS, XLSX, CSV, images. Max 1&nbsp;GB per file
                  (server limits may apply).
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

            <!-- PART 4: SLA SECTION -->
              <section class="notice-section card" id="part4-sla">
                <h2 class="section-title">SERVICE LEVEL AGREEMENT (SLA)</h2>
                <p><strong>SLA.</strong> To manage expectations, please refer to our SLA for the following contracts below:</p>

                <ul>
                  <li>
                    For <strong>Globe</strong> contracts, NDAs, DPAs, DSAs and DP clauses:
                    <br><strong>THREE (3)</strong> business days from time of request
                  </li>
                  <li>
                    For <strong>Globe Group</strong> and <strong>third party/vendor</strong> contracts, NDAs, DPAs, DSAs, and DP clauses:
                    <br><strong>FIVE (5)</strong> business days from time of request
                  </li>
                </ul>

                <p class="notice">
                  <strong>NOTICE:</strong> Please be informed that effective <strong>September 1, 2025</strong>,
                  all contract review assigned to <strong>Atty. Alex</strong> will be temporarily reassigned to
                  <strong>Atty. Franz</strong>.
                </p>
              </section>

      <div class="actions">
        <button type="submit" class="btn primary">Submit Request</button>
        <button type="reset"  class="btn">Clear</button>
      </div>
    </form>

  </main>
  <script src="/assets/js/form-routing.js"></script>
</body>
</html>

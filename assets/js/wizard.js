(function () {
  const form = document.getElementById('requestForm');
  const steps = Array.from(document.querySelectorAll('.wizard-pane'));
  const stepDots = Array.from(document.querySelectorAll('#wizardSteps .wizard-step'));
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const submitBtn = document.getElementById('submitBtn');
  const resetBtn = document.getElementById('resetBtn');

  let current = 0; // index 0..2

  function showStep(i) {
    // panes
    steps.forEach((s, idx) => {
      if (idx === i) {
        s.removeAttribute('hidden');
      } else {
        s.setAttribute('hidden', 'hidden');
      }
    });

    // progress bar states
    stepDots.forEach((dot, idx) => {
      dot.classList.toggle('is-active', idx === i);
      dot.classList.toggle('is-complete', idx < i);
    });

    // buttons
    prevBtn.disabled = i === 0;
    nextBtn.hidden = i === steps.length - 1;
    submitBtn.hidden = !(i === steps.length - 1);
  }

  function validateCurrentStep() {
    // only validate inputs inside the visible step
    const pane = steps[current];
    // Temporarily mark non-current fields as not required so reportValidity doesn't catch them
    const toggled = [];
    form.querySelectorAll('[required]').forEach(el => {
      if (!pane.contains(el)) {
        el.dataset.wasRequired = '1';
        el.required = false;
        toggled.push(el);
      }
    });

    const ok = form.reportValidity();

    // restore required flags
    toggled.forEach(el => {
      el.required = true;
      delete el.dataset.wasRequired;
    });

    return ok;
  }

  prevBtn.addEventListener('click', () => {
    if (current > 0) {
      current -= 1;
      showStep(current);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });

  nextBtn.addEventListener('click', () => {
    if (!validateCurrentStep()) return;
    if (current < steps.length - 1) {
      current += 1;
      showStep(current);
      window.scrollTo({ top: 0, behavior: 'smooth' });
    }
  });

  resetBtn.addEventListener('click', () => {
    current = 0;
    showStep(current);
  });

  // Attachments counter
  const attach = document.getElementById('attachments');
  const attachHint = document.getElementById('attachHint');
  if (attach && attachHint) {
    attach.addEventListener('change', () => {
      const count = attach.files?.length || 0;
      attachHint.textContent = `${count} of 5 files selected.`;
    });
  }

  // Conditional UI: prev reviewed -> show ticket
  const prevYesNo = form.querySelectorAll('input[name="prev_reviewed"]');
  const prevWrap = document.getElementById('prevTicketWrap');
  prevYesNo.forEach(r => {
    r.addEventListener('change', () => {
      prevWrap.style.display = (r.value === 'Yes' && r.checked) ? '' : 'none';
    });
  });

  // Contract type "Other"
  const contractOtherRadio = document.getElementById('contract_other_radio');
  const contractOtherInput = document.getElementById('contract_other');
  if (contractOtherRadio && contractOtherInput) {
    const syncOther = () => {
      const isOther = contractOtherRadio.checked;
      contractOtherInput.style.display = isOther ? '' : 'none';
      contractOtherInput.required = isOther;
      if (!isOther) contractOtherInput.value = '';
    };
    form.addEventListener('change', (e) => {
      if (e.target.name === 'contract_type') syncOther();
    });
    syncOther();
  }

  // PD Nature "Others"
  const pdOtherRadio = document.getElementById('pd_other_radio');
  const pdOtherText = document.getElementById('pd_other_text');
  if (pdOtherRadio && pdOtherText) {
    const syncPdOther = () => {
      const isOther = pdOtherRadio.checked;
      pdOtherText.style.display = isOther ? '' : 'none';
      pdOtherText.required = isOther;
      if (!isOther) pdOtherText.value = '';
    };
    form.addEventListener('change', (e) => {
      if (e.target.name === 'pd_nature') syncPdOther();
    });
    syncPdOther();
  }

  // Group -> Tribe toggle
  const groupSel = document.getElementById('group');
  const tribeBlock = document.getElementById('tribe-block');
  if (groupSel && tribeBlock) {
    const syncTribe = () => {
      const show = groupSel.value === 'B2B';
      tribeBlock.style.display = show ? '' : 'none';
      const tribe = document.getElementById('tribe');
      if (tribe) tribe.required = show;
    };
    groupSel.addEventListener('change', syncTribe);
    syncTribe();
  }

  // Initial view
  showStep(current);
})();

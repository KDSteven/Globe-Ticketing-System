(function () {
  // Elements
  const viewLogin      = document.getElementById('view-login');
  const viewReset      = document.getElementById('view-reset');
  const gotoForgot     = document.getElementById('gotoForgot');
  const backToLogin    = document.getElementById('backToLogin');
  const stepEmailForm  = document.getElementById('stepEmailForm');
  const fpEmail        = document.getElementById('fp_email');
  const sendOtpBtn     = document.getElementById('sendOtpBtn');
  const emailStepHint  = document.getElementById('emailStepHint');
  const stepResetForm  = document.getElementById('stepResetForm');
  const resetEmail     = document.getElementById('reset_email');
  const otpInput       = document.getElementById('otp');
  const otpCountdown   = document.getElementById('otpCountdown');
  const resendOtpBtn   = document.getElementById('resendOtpBtn');
  const pwBlock        = document.getElementById('pwBlock');
  const newPw          = document.getElementById('new_password');
  const confirmPw      = document.getElementById('confirm_password');
  const resetBtn       = document.getElementById('resetBtn');

  let otpTimer = null;
  let otpVerified = false;
  let countdownEnd = 0;

  const sleep = (ms) => new Promise(r => setTimeout(r, ms));

  // ========== VIEW SWITCH ==========
  function show(view) {
    if (view === 'reset') {
      viewLogin.classList.add('is-hidden');
      viewLogin.classList.remove('is-active');
      viewReset.classList.remove('is-hidden');
      viewReset.classList.add('is-active');
    } else {
      viewReset.classList.add('is-hidden');
      viewReset.classList.remove('is-active');
      viewLogin.classList.remove('is-hidden');
      viewLogin.classList.add('is-active');
    }
  }

  // Buttons
  gotoForgot?.addEventListener('click', () => {
    show('reset');
    stepEmailForm.hidden = false;
    stepResetForm.hidden = true;
    pwBlock.hidden = true;
    otpInput.value = '';
    otpVerified = false;
    resendOtpBtn.disabled = true;
    emailStepHint.textContent = "We’ll email a 6-digit code that expires in 10 minutes.";
  });

  backToLogin?.addEventListener('click', () => show('login'));

  // ========== HELPERS ==========
  function setBtnLoading(btn, isLoading) {
    const text = btn.querySelector('.btn-text');
    const spin = btn.querySelector('.btn-spinner');
    text.hidden = !!isLoading;
    spin.hidden = !isLoading;
    btn.disabled = !!isLoading;
  }

  function startCountdown(seconds = 600) {
    countdownEnd = Date.now() + seconds * 1000;
    updateCountdown();
    otpTimer && clearInterval(otpTimer);
    otpTimer = setInterval(updateCountdown, 1000);
  }

  function updateCountdown() {
    const diff = Math.max(0, Math.floor((countdownEnd - Date.now()) / 1000));
    const mm = String(Math.floor(diff / 60)).padStart(2, '0');
    const ss = String(diff % 60).padStart(2, '0');
    otpCountdown.textContent = `${mm}:${ss}`;
    resendOtpBtn.disabled = diff > 540;
    if (diff === 0) clearInterval(otpTimer);
  }

  function revealPwBlock() {
    if (!pwBlock.hidden) return;
    pwBlock.hidden = false;
    pwBlock.removeAttribute('aria-hidden');
    newPw.required = true;
    confirmPw.required = true;
  }

  // ========== STEP 1: SEND OTP ==========
  stepEmailForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = fpEmail.value.trim();
    if (!email) {
      toast.warning('Missing Email', 'Please enter your email before proceeding.');
      return;
    }

    setBtnLoading(sendOtpBtn, true);
    try {
      const res = await fetch('/api/send_reset_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email })
      });
      const json = await res.json();

      if (json.ok) {
        resetEmail.value = email;
        stepEmailForm.hidden = true;
        stepResetForm.hidden = false;
        otpInput.focus();

        startCountdown(600);
        resendOtpBtn.disabled = true;
        emailStepHint.textContent = "OTP sent. Check your inbox (and spam).";

        // ✅ Success toast
        toast.success('OTP Sent!', 'A verification code has been sent to your Globe email.');
      } else {
        toast.error('Failed', json.error || 'Unable to send OTP. Please try again.');
      }
    } catch (err) {
      toast.error('Network Error', 'Could not contact the server. Try again.');
    } finally {
      setBtnLoading(sendOtpBtn, false);
    }
  });

  // ========== RESEND OTP ==========
  resendOtpBtn?.addEventListener('click', async () => {
    if (resendOtpBtn.disabled) return;
    resendOtpBtn.disabled = true;
    try {
      const res = await fetch('/api/send_reset_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: resetEmail.value })
      });
      const json = await res.json();

      if (json.ok) {
        startCountdown(600);
        toast.info('OTP Resent', 'A new 6-digit code has been sent to your email.');
      } else {
        toast.error('Resend Failed', json.error || 'Please try again later.');
        resendOtpBtn.disabled = false;
      }
    } catch {
      toast.error('Network Error', 'Unable to resend OTP.');
      resendOtpBtn.disabled = false;
    }
  });

  // ========== STEP 2A: VERIFY OTP ==========
  otpInput?.addEventListener('input', async () => {
    if (otpVerified) return;
    const code = otpInput.value.replace(/\D/g, '');
    if (code.length !== 6) return;

    otpInput.readOnly = true;
    try {
      const res = await fetch('/api/verify_reset_otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: resetEmail.value, otp: code })
      });
      const json = await res.json();

      if (json.ok) {
        otpVerified = true;
        revealPwBlock();
        toast.success('OTP Verified', 'You can now set a new password.');
        newPw.focus();
      } else {
        otpInput.readOnly = false;
        otpInput.select();
        toast.error('Invalid OTP', json.error || 'Please check your code and try again.');
      }
    } catch {
      otpInput.readOnly = false;
      toast.error('Network Error', 'Failed to verify OTP.');
    }
  });

  // ========== STEP 2B: RESET PASSWORD ==========
  stepResetForm?.addEventListener('submit', async (e) => {
    e.preventDefault();
    if (!otpVerified) {
      toast.warning('Unverified', 'Please verify your OTP first.');
      return;
    }

    const p1 = newPw.value.trim();
    const p2 = confirmPw.value.trim();

    if (p1.length < 8 || !/[0-9]/.test(p1) || !/[A-Za-z]/.test(p1)) {
      toast.warning('Weak Password', 'Use at least 8 characters with letters and numbers.');
      newPw.focus();
      return;
    }
    if (p1 !== p2) {
      toast.error('Mismatch', 'Passwords do not match.');
      confirmPw.focus();
      return;
    }

    setBtnLoading(resetBtn, true);
    try {
      const res = await fetch('/api/reset_password.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ email: resetEmail.value, otp: otpInput.value, new_password: p1 })
      });
      const json = await res.json();

      if (json.ok) {
        toast.success('Password Updated', 'You can now sign in with your new password.');
        await sleep(2000);
        show('login');
      } else {
        toast.error('Failed', json.error || 'Unable to reset password.');
      }
    } catch {
      toast.error('Network Error', 'Could not connect to the server.');
    } finally {
      setBtnLoading(resetBtn, false);
    }
  });

  // ========== PASSWORD SHOW/HIDE ==========
  document.addEventListener('click', (e) => {
    const t = e.target.closest('.pw-toggle');
    if (!t) return;
    const sel = t.getAttribute('data-toggle');
    const input = document.querySelector(sel);
    if (!input) return;
    input.type = input.type === 'password' ? 'text' : 'password';
  });

  // Initial view
  show('login');
})();

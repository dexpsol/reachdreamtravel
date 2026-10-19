(() => {
  const password = document.getElementById('loginPassword');
  const toggle = document.querySelector('.login-password-toggle');
  if (!password || !toggle) return;

  toggle.addEventListener('click', () => {
    const visible = password.type === 'password';
    password.type = visible ? 'text' : 'password';
    const label = visible ? 'Hide password' : 'Show password';
    toggle.querySelector('i').className = visible ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye';
    toggle.setAttribute('aria-label', label);
    toggle.setAttribute('title', label);
    toggle.setAttribute('aria-pressed', String(visible));
  });
})();

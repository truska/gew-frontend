(() => {
  const announcement = document.querySelector('[data-announcement]');
  const toggle = announcement?.querySelector('.announcement-toggle');
  const dismiss = announcement?.querySelector('.announcement-dismiss');
  if (!announcement || !toggle) return;

  const setExpanded = (expanded) => {
    announcement.classList.toggle('is-expanded', expanded);
    announcement.classList.toggle('is-collapsed', !expanded);
    announcement.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    toggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
  };

  setExpanded(announcement.getAttribute('aria-expanded') === 'true');
  toggle.addEventListener('click', () => {
    setExpanded(announcement.getAttribute('aria-expanded') !== 'true');
  });

  dismiss?.addEventListener('click', () => {
    const cookieName = announcement.dataset.cookieName;
    const days = Number.parseInt(announcement.dataset.cookieDays || '7', 10);

    if (cookieName) {
      const expires = new Date(Date.now() + Math.max(1, days) * 86400000).toUTCString();
      const secure = window.location.protocol === 'https:' ? '; Secure' : '';
      document.cookie = `${encodeURIComponent(cookieName)}=1; expires=${expires}; path=/; SameSite=Lax${secure}`;
    }

    announcement.remove();
  });
})();

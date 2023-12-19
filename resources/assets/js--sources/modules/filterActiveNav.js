export const filterActiveNav = () => {
  const links = document.querySelectorAll('[data-link]');
  const cleanPath = window.location.origin + window.location.pathname;

  for (let i = 0; i < links.length; i++) {
    const link = links[i];

    if (link.href === cleanPath) link.classList.add('is-active');
  }
};

filterActiveNav();

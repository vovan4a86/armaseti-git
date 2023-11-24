import $ from 'jquery';

export const filterLinks = () => {
  const $link = $('[data-link]');
  const cleanPath = window.location.origin + window.location.pathname;

  $link.filter('[href="' + cleanPath + '"]').addClass('is-active');
};

filterLinks();

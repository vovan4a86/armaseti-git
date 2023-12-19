import $ from 'jquery';

export const mobileSubmenu = () => {
  const $subLink = $('[data-sublink]');

  $subLink.on('click', function (e) {
    const $target = e.target;
    const $subMenu = $(this).siblings('[data-submenu]');

    if ($target.closest('[data-sublink-trigger]')) {
      e.preventDefault();

      if ($subMenu.is(':visible')) {
        closeMenus();
        return;
      }

      closeMenus();

      $subMenu.slideDown();
      $(this).addClass('is-active');
    }
  });

  function closeMenus() {
    $subLink.removeClass('is-active');
    $('[data-submenu]').slideUp();
  }
};

mobileSubmenu();

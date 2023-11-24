import $ from 'jquery';

export const preloader = () => {
  $('.preloader').fadeOut();
  $('body').removeClass('no-scroll');
};

export const utils = () => {
  const blocks = '.lazy, picture, img, video';

  $(blocks).on('dragstart', () => false);
  // $(blocks).on('contextmenu', () => false);
};

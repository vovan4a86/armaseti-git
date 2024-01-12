import $ from 'jquery';

export const utils = () => {
  const blocks = '.lazy, picture, img, video';

  $(blocks).on('dragstart', () => false);
  // $(blocks).on('contextmenu', () => false);
};

utils();

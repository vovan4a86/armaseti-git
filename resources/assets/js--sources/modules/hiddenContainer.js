import $ from 'jquery';

export const hiddenContainer = () => {
  const $hide_container = $('.js-hide_container');

  $hide_container.each(function () {
    const root = $(this);
    const inn = root.find('.js-hide_container__inn');
    const btn = root.find('.js-hide_container__btn');
    const scrollHeight = inn[0].scrollHeight;
    const cssHeight = inn.height();

    root.data('cssheight', cssHeight);

    if (scrollHeight <= cssHeight) {
      btn.hide();
    } else {
      btn.show();
    }
  });

  $('.js-hide_container__btn').on('click', function () {
    const root = $(this).closest('.js-hide_container');
    const inn = root.find('.js-hide_container__inn');
    const btn = root.find('.js-hide_container__btn');
    const dynamicInnHeight = inn.height();
    const staticInnHeight = root.data('cssheight');
    const scrollHeight = inn[0].scrollHeight;

    if (scrollHeight > dynamicInnHeight) {
      inn.animate({
        height: scrollHeight + 'px'
      });
      inn.addClass('is-open');
      btn.addClass('is-opened-hc');
    } else {
      inn.animate({
        height: staticInnHeight + 'px'
      });
      inn.removeClass('is-open');
      btn.removeClass('is-opened-hc');
    }
  });
};

hiddenContainer();

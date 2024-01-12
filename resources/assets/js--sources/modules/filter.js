import $ from 'jquery';

export const filter = () => {
  const $filter = $('.b-filter');

  if (!$filter) return;

  $filter.on('click', '.b-filter__title', function () {
    $(this).toggleClass('is-active');
    $(this).next().slideToggle('fast');
  });

  $filter.on('click', '.b-filter__btn', function () {
    const $hidden = $(this).closest('.b-filter__body').find('.b-filter__hidden');

    if ($hidden.is(':visible')) {
      $hidden.slideUp('fast');
      $(this).text('Показать все');
    } else {
      $hidden.slideDown('fast');
      $(this).text('Скрыть');
    }
  });
};

filter();

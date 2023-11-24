import $ from 'jquery';

const lightValidation = () => {
  const forms = $('form');

  forms.on('click', 'button', function (e) {
    const isEmpty = field => $.trim(field.val()) === '';

    const form = $(this).closest('form');

    form.find(':input').each(function () {
      const field = $(this);

      if ((isEmpty(field) || (field.is(':checkbox') && !field.is(':checked'))) && field.is('[required]')) {
        e.preventDefault();
        field.addClass('invalid');
      } else {
        field.removeClass('invalid');
      }
    });
  });

  forms.find(':input').on('click', function () {
    $(this).removeClass('invalid');
  });

  forms.find('.checkbox').on('click', function () {
    $(this).closest('label').find('input').removeClass('invalid');
  });
};

lightValidation();

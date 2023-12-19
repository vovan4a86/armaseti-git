import $ from 'jquery';

export const fileUpload = () => {
  $('.upload input').on('change', function (e) {
    e.preventDefault();

    $(this).closest('.upload').find('.upload__name').text(getFileName(this.value));
    $('.popup__file-label').addClass('v-hidden');

    function getFileName(prop) {
      return prop.substr(prop.lastIndexOf('\\') + 1);
    }
  });
};

fileUpload();

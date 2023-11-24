import Inputmask from 'inputmask';

////
export const maskedInputs = ({ phoneSelector, emailSelector, humanIdSelector }) => {
  const phones = document.querySelectorAll(phoneSelector);
  const emails = document.querySelectorAll(emailSelector);
  const humanId = document.querySelectorAll(humanIdSelector);

  const phoneParams = {
    mask: '+7 (999) 999-99-99',
    showMaskOnHover: false
  };

  const humanIdParams = {
    mask: '99 9999 9999',
    showMaskOnHover: false
  };

  const emailParams = { showMaskOnHover: false };

  phones &&
    phones.forEach(phone => {
      Inputmask(phoneParams).mask(phone);
    });

  emails &&
    emails.forEach(email => {
      Inputmask('email', emailParams).mask(email);
    });

  humanId &&
    humanId.forEach(innField => {
      Inputmask(humanIdParams).mask(innField);
    });
};

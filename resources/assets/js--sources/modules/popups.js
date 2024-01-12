import { Fancybox } from '@fancyapps/ui';

export const closeBtn =
  '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M25 7 7 25M25 25 7 7"/></svg>';

Fancybox.bind('[data-fancybox]', {
  closeButton: 'outside',
  showClass: 'f-fadeIn',
  hideClass: 'f-fadeOut',
  infinite: false
});

Fancybox.bind('[data-popup]', {
  mainClass: 'popup--custom',
  template: { closeButton: closeBtn },
  showClass: 'f-fadeIn',
  hideClass: 'f-fadeOut',
  hideScrollbar: false
});

Fancybox.bind('[data-cities]', {
  mainClass: 'popup--custom popup--ajax',
  template: { closeButton: closeBtn },
  showClass: 'f-fadeIn',
  hideClass: 'f-fadeOut'
});

Fancybox.bind('[data-product-popup]', {
  mainClass: 'popup--custom popup--order',
  template: { closeButton: closeBtn },
  showClass: 'f-fadeIn',
  hideClass: 'f-fadeOut',
  hideScrollbar: false,
  on: {
    reveal: (e, trigger) => {
      const popup = e.$container;
      const label = trigger.label;

      if (popup && label) {
        const popupLabel = popup.querySelector('.popup__label');
        if (popupLabel) popupLabel.textContent = label;
      }
    },
    close: (e, trigger) => {
      const popup = e.$container;
      const label = trigger.label;

      if (popup && label) {
        const popupLabel = popup.querySelector('.popup__label');
        if (popupLabel) popupLabel.textContent = '';
      }
    }
  }
});

const closePopupButtons = document.querySelectorAll('[data-close-popup]');

closePopupButtons?.forEach(btn => {
  btn.addEventListener('click', e => {
    e.preventDefault();
    Fancybox.close();
  });
});

export const showSuccessRequestDialog = () => {
  Fancybox.show([{ src: '#request-done', type: 'inline' }], {
    mainClass: 'popup--custom popup--complete',
    template: { closeButton: closeBtn },
    showClass: 'f-fadeIn',
    hideClass: 'f-fadeOut'
  });
};

// в свой модуль форм, импортируешь функцию вызова «спасибо» → вызываешь on success
// import { showSuccessRequestDialog } from 'путь до компонента'
// вызываешь где нужно
// showSuccessRequestDialog();

import Alpine from 'alpinejs';

export const alpineJsInit = () => {
  window.Alpine = Alpine;
  Alpine.start();
};

alpineJsInit();

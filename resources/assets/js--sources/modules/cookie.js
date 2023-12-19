import Cookies from 'js-cookie';

export const cookie = () => {
  const cookieBlock = document.querySelector('.b-cookie');
  const close = document.querySelector('.b-cookie__close');

  const handleClick = () => {
    cookieBlock.classList.remove('is-active');
    Cookies.set('__armaseti-cookie', 'true', { expires: 30 });
  };

  if (close) {
    close.addEventListener('click', handleClick);
  }

  if (!Cookies.get('__armaseti-cookie')) {
    setTimeout(() => {
      cookieBlock.classList.add('is-active');
    }, 3000);
  }
};

cookie();

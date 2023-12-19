import { setThrottling } from '../functions/throttling';

export const scrollTop = ({ trigger }) => {
  const scrollTop = document.querySelector(trigger);

  const manageTrigger = () => {
    if (scrollTop) {
      scrollTop.classList.toggle('is-active', window.scrollY >= 300);
    }
  };

  const scrollToTop = () => {
    window.scroll({
      top: 0,
      behavior: 'smooth'
    });
  };

  const optimizedHandler = setThrottling(manageTrigger, 100);

  scrollTop?.addEventListener('click', scrollToTop);
  window.addEventListener('scroll', optimizedHandler);
};

scrollTop({ trigger: '.scrolltop' });

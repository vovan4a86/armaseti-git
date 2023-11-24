import { setThrottling } from '../functions/throttling';

export const scrollTop = ({ trigger }) => {
  const scrollTop = document.querySelector(trigger);

  const manageTrigger = () => {
    if (scrollTop) {
      window.scrollY >= 300 ? scrollTop.classList.add('is-active') : scrollTop.classList.remove('is-active');

      optimizedHandler();

      scrollTop.addEventListener('click', () => {
        window.scroll({
          top: 0,
          behavior: 'smooth'
        });
      });
    }
  };

  const optimizedHandler = setThrottling(manageTrigger, 100);

  optimizedHandler();

  window.addEventListener('scroll', optimizedHandler);
};

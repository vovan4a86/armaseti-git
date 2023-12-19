export const counter = () => {
  const counters = document.querySelectorAll('[data-counter]');

  counters.forEach(counter => {
    counter.addEventListener('click', function (e) {
      const input = this.querySelector('[data-count]');
      const target = e.target;

      if (target.closest('.b-counter__btn--prev') && input.value > 1) {
        input.value--;
      } else if (target.closest('.b-counter__btn--next')) {
        input.value++;
      }
    });

    const input = counter.querySelector('[data-count]');
    input.addEventListener('change', function () {
      if (this.value < 0 || this.value === '0' || this.value === '') {
        this.value = 1;
      }
    });
  });
};

counter();

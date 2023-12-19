import Swiper, { Pagination, Autoplay } from 'swiper';

export const cardsSlider = ({ sliders, sliderPagination }) => {
  const sliderContainers = document.querySelectorAll(sliders);

  if (!sliderContainers) return;

  sliderContainers.forEach(sliderContainer => {
    const pagination = sliderContainer.querySelector(sliderPagination);

    new Swiper(sliderContainer, {
      modules: [Pagination, Autoplay],
      speed: 600,
      grabCursor: true,
      autoplay: {
        delay: 5500,
        disableOnInteraction: false
      },
      pagination: {
        el: pagination,
        clickable: true
      },
      observer: true,
      observeParents: true
    });
  });
};

cardsSlider({
  sliders: '[data-card-slider]',
  sliderPagination: '[data-card-pagination]'
});

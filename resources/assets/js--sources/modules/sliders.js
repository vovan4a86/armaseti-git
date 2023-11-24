import Swiper, { EffectFade, Navigation, Thumbs } from 'swiper';

export const reviewsSlider = ({ slider, navigationNext }) => {
  new Swiper(slider, {
    modules: [Navigation],
    slidesPerView: 1.3,
    centeredSlides: false,
    spaceBetween: 10,
    autoplay: {
      delay: 3000,
      disableOnInteraction: false
    },
    navigation: {
      nextEl: navigationNext
    },
    speed: 600,
    breakpoints: {
      600: {
        slidesPerView: 1.3,
        spaceBetween: 20,
        centeredSlides: false
      },
      810: {
        slidesPerView: 2.2,
        spaceBetween: 10,
        centeredSlides: false
      },
      1080: {
        slidesPerView: 1.3,
        spaceBetween: 30,
        centeredSlides: false
      },
      1280: {
        slidesPerView: 2.2,
        spaceBetween: 30,
        centeredSlides: false
      },
      1440: {
        slidesPerView: 2.4,
        spaceBetween: 30,
        centeredSlides: false
      }
    }
  });
};

export const productSlider = ({ mainSlider, navigationSlider, navigationNext, navigationPrev }) => {
  const navSlider = new Swiper(navigationSlider, {
    modules: [Navigation, Thumbs],
    direction: 'vertical',
    spaceBetween: 6,
    slidesPerView: 4,
    speed: 1200,
    watchSlidesProgress: true,
    navigation: {
      nextEl: navigationNext,
      prevEl: navigationPrev
    }
  });

  new Swiper(mainSlider, {
    modules: [Thumbs, EffectFade],
    fadeEffect: { crossFade: true },
    effect: 'fade',
    speed: 1200,
    thumbs: {
      swiper: navSlider
    }
  });
};

productSlider({
  mainSlider: '[data-product-slider]',
  navigationNext: '[data-product-next]',
  navigationPrev: '[data-product-prev]',
  navigationSlider: '[data-product-slider-nav]'
});

reviewsSlider({
  slider: '[data-reviews]',
  navigationNext: '[data-review-next]'
});

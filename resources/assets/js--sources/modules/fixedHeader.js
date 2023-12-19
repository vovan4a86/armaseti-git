import hcSticky from 'hc-sticky';
import { setThrottling } from '../functions/throttling';
import $ from 'jquery';

export const stickyHeader = ({ headerSelector }) => {
  new hcSticky(headerSelector);

  const $header = $(headerSelector);
  const $headerTop = $('.header__top');
  const $headerBody = $('.header__body');

  const getSpacerHeight = () => {
    const topHeight = $headerTop.is(':visible') ? $headerTop.outerHeight() : 0;
    const bodyHeight = $headerBody.is(':visible') ? $headerBody.outerHeight() : 0;
    return topHeight + bodyHeight;
  };

  const manageTrigger = () => {
    if ($header) {
      const scrollY = window.scrollY;
      const translateY = scrollY >= 150 ? `-${getSpacerHeight()}px` : '0';
      $header.css('transform', `translateY(${translateY})`);
      $header.toggleClass('sticky', scrollY >= 150);
      optimizedHandler();
    }
  };

  const optimizedHandler = setThrottling(manageTrigger, 100);

  window.addEventListener('scroll', optimizedHandler);
};

stickyHeader({
  headerSelector: '.header'
});

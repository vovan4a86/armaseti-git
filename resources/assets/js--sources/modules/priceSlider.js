import $ from 'jquery';
import ionRangeSlider from 'ion-rangeslider';

export const priceSlider = () => {
  const rangeSliders = $('[data-range-slider]');

  if (!rangeSliders.length) return;

  rangeSliders.each(function () {
    const range = $(this).find('.js-range-slider');
    const inputFrom = $(this).find('.js-input-from');
    const inputTo = $(this).find('.js-input-to');
    const minPrice = inputFrom.data('price-from');
    const maxPrice = inputTo.data('price-to');
    let fromPrice = minPrice;
    let toPrice = maxPrice;

    range.ionRangeSlider({
      skin: 'round',
      type: 'double',
      min: minPrice,
      max: maxPrice,
      from: minPrice,
      to: maxPrice,
      step: 100,
      onStart: updateInputs,
      onChange: updateInputs
    });

    const sliderInstance = range.data('ionRangeSlider');

    function updateInputs(data) {
      const { from, to } = data;

      inputFrom.val(from);
      inputTo.val(to);
    }

    inputFrom.on('input', function () {
      let value = $(this).prop('value');

      if (value < minPrice) {
        value = minPrice;
      } else if (value > toPrice) {
        value = toPrice;
      }

      sliderInstance.update({
        from: value
      });
    });

    inputTo.on('input', function () {
      let value = $(this).prop('value');

      if (value < fromPrice) {
        value = fromPrice;
      } else if (value > maxPrice) {
        value = maxPrice;
      }

      sliderInstance.update({
        to: value
      });
    });
  });
};

priceSlider();

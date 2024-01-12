export const truncateText = ({ el, maxlength }) => {
  const data = document.querySelectorAll(el);

  data?.forEach(str => {
    const tmp = str.textContent;
    if (tmp) str.textContent = truncate(tmp, maxlength);
  });

  function truncate(str, maxlength) {
    return str.length > maxlength ? str.slice(0, maxlength - 1) + '…' : str;
  }
};

truncateText({
  el: '.s-card__title',
  maxlength: 34
});

truncateText({
  el: '.prod-card__title',
  maxlength: 40
});

truncateText({
  el: '.news-card__title',
  maxlength: 60
});

truncateText({
  el: '.cart-item__title',
  maxlength: 40
});

truncateText({
  el: '.a-newses__title',
  maxlength: 70
});

truncateText({
  el: '.a-newses__text',
  maxlength: 75
});

truncateText({
  el: '.card__title',
  maxlength: 70
});

truncateText({
  el: '.card__text',
  maxlength: 75
});

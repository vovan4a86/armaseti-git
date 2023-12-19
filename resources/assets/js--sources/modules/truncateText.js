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

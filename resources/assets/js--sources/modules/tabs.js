export const tabs = ({ tabSelector, tabLink, tabView }) => {
  const tabsContainers = document.querySelectorAll(tabSelector);

  tabsContainers?.forEach(tabsContainer => {
    tabsContainer.addEventListener('click', function (e) {
      const target = e.target;

      if (target.dataset.open) {
        const targetView = target.dataset.open;
        const views = tabsContainer.querySelectorAll(tabView);
        const links = tabsContainer.querySelectorAll(tabLink);

        if (views && links) {
          // set active tab link
          links.forEach(link => link.classList.remove('is-active'));
          target.classList.add('is-active');

          // set active tab view
          views.forEach(view => {
            view.classList.remove('is-active');
            if (view.dataset.view === targetView) {
              view.classList.add('is-active');
            }
          });
        }
      }
    });
  });
};

tabs({
  tabSelector: '[data-tabs]',
  tabLink: '[data-open]',
  tabView: '[data-view]'
});

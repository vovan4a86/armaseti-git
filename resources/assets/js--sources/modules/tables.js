const wrappingTables = () => {
  let tables = document.getElementsByTagName('table'),
    length = tables.length,
    i,
    wrapper;

  for (i = 0; i < length; i++) {
    wrapper = document.createElement('div');
    wrapper.setAttribute('class', 'hscroll');
    tables[i].parentNode.insertBefore(wrapper, tables[i]);
    wrapper.appendChild(tables[i]);
  }
};

wrappingTables();

export const setThrottling = (call, timeout) => {
  let timer = null;

  return function perform(...args) {
    if (timer) return;

    timer = setTimeout(() => {
      call(...args);

      clearTimeout(timer);
      timer = null;
    }, timeout);
  };
};

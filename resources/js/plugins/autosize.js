import autosize from 'autosize';

export const install = (Vue, options) => {
  Vue.directive('autosize', {
    bind(el) {
      autosize(el);

      el.addEventListener('focus', () => autosize.update(el));
    },

    componentUpdated(el) {
      autosize.update(el);
    },

    unbind(el) {
      autosize.destroy(el);
    }
  });
};

export default install;

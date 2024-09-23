import autosize from 'autosize';

export const autosizeDirective = {
  beforeMount(el) {
    autosize(el);
    el.addEventListener('focus', () => autosize.update(el));
  },
  updated(el) {
    autosize.update(el);
  },
  unmounted(el) {
    autosize.destroy(el);
  },
};

import autosize from 'autosize';

export const autosizeDirective = {
  bind(el) {
    autosize(el);
    el.addEventListener('focus', () => autosize.update(el));
  },
  componentUpdated(el) {
    autosize.update(el);
  },
  unbind(el) {
    autosize.destroy(el);
  },
};

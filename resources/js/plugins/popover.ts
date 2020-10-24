import Session from '../libs/session';
import VuePopover from '../components/popover.vue';
import Popper from "popper.js";
import Vue from 'vue';

const Popover = {
  install() {
    const nodes = document.querySelectorAll('[data-popover]');

    if (!nodes.length) {
      return;
    }

    let popover = JSON.parse(Session.getItem('popover', '[]'));

    for (let node of nodes) {
      const options = JSON.parse((node as HTMLElement).dataset.popover as string);

      if (popover.includes(options.message)) {
        continue;
      }

      const wrapper = new VuePopover({propsData: {message: options.message, placement: options.placement}}).$mount();
      const el = document.body;

      if (el !== null) {
        el.appendChild(wrapper.$el);
      }

      let popperOptions = {};

      if (options.placement) {
        popperOptions['placement'] = options.placement;
      }

      if (options.offset) {
        popperOptions = Object.assign(popperOptions, {modifiers: { offset: { offset: options.offset } }});
      }

      new Popper(node, wrapper.$el, popperOptions);
    }
  }
};

Vue.use(Popover);

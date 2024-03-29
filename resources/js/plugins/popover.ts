import Session from '../libs/session';
import VuePopover from '../components/popover.vue';
import { createPopper } from '@popperjs/core';
import Vue from 'vue';

const Popover = {
  install() {
    let popover = JSON.parse(Session.getItem('popover', '[]'));

    function installPopover(node: HTMLElement) {
      const options = JSON.parse((node as HTMLElement).dataset.popover as string);

      if (popover.includes(options.message)) {
        return;
      }

      const wrapper = new VuePopover({propsData: {message: options.message, placement: options.placement}}).$mount();

      node.parentNode!.insertBefore(wrapper.$el, node.nextSibling);
      delete node.dataset.popover;

      let popperOptions = {};

      if (options.placement) {
        popperOptions['placement'] = options.placement;
      }

      if (options.offset) {
        popperOptions = Object.assign(popperOptions, {modifiers: [{ name: 'offset', options: { offset: options.offset } }]});
      }

      createPopper(node, wrapper.$el as HTMLElement, popperOptions);
    }

    function waitForPopover() {
      const nodes = document.querySelectorAll('[data-popover]');

      for (let node of nodes) {
        installPopover(node as HTMLElement);
      }
    }

    const observer = new MutationObserver(waitForPopover);
    observer.observe(document.body, { attributes: false, childList: true, subtree: false });
  }
};

Vue.use(Popover);

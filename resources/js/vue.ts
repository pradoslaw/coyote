import Vue from "vue";
import VueNotifications from "vue-notification";
import {VueInstance} from "../../survey/src/vue";
import axiosErrorHandler from './libs/axios-error-handler.js';
import store from "./store/index";

export function nextTick(block: () => void): void {
  Vue.nextTick(block);
}

export function createVueApp(name: string, selector: string, component: object): Vue {
  return new Vue({...component, name, el: selector});
}

export function createVueAppNotifications(name: string, selector: string, component: object): void {
  Vue.use(VueNotifications, {componentName: 'vue-library-notifications'});
  Vue.component('vue-notifications', {
    template: `
      <vue-library-notifications position="bottom right"/>
    `
  })
  new Vue({...component, name, el: selector});
}

export function setAxiosErrorVueNotification(): void {
  axiosErrorHandler((message: string) => Vue.notify({type: 'error', text: message}));
}

export function createVueAppPhantom(component: object, properties: object): Element {
  const VuePhantom = Vue.extend(component);
  const app = new VuePhantom({propsData: properties, store});
  return app.$mount().$el;
}

export function createVueAppGhost(component: object, properties: object, eventListeners: object): [VueInstance, Element] {
  const VueGhost = Vue.extend(component);
  const app = new VueGhost({propsData: properties});
  app.$mount();
  for (const [event, listener] of Object.entries(eventListeners)) {
    app.$on(event, listener);
  }
  return [app, app.$el];
}

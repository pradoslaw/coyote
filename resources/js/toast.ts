import VueNotifications, {notify as libraryNotify} from "@kyvg/vue3-notification";

interface ToastOptions {
  text?: string;
  title?: string;
  type?: 'error' | 'success';
  duration?: number;
  clean?: boolean;
}

export function notify(options: ToastOptions): void {
  libraryNotify(options);
}

export function install(app) {
  app.use(VueNotifications, {componentName: 'vue-library-notifications'});
  app.component('vue-notifications', {
    template: '<vue-library-notifications :dangerously-set-inner-html="true" position="bottom right"/>',
  });
}

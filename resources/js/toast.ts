import Vue from 'vue';

interface ToastOptions {
  text?: string;
  title?: string;
  type?: 'error' | 'success';
  duration?: number;
  clean?: boolean;
}

export function notify(options: ToastOptions): void {
  Vue.notify(options);
}

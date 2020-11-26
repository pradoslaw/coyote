interface ModalOptions {
  message: string;
  title: string;
  okLabel: string;
}

declare module 'vue-modals' {
  import Vue, { PluginFunction } from 'vue'

  module "vue/types/vue" {
    interface Vue {
      $confirm(options: ModalOptions): Promise<any>;
    }
  }

  class VueModals {
    static install: PluginFunction<never>
  }
}

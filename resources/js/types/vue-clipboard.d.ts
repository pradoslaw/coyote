declare module 'vue-clipboard' {
  import Vue, { PluginFunction } from 'vue'

  module "vue/types/vue" {
    interface Vue {
      $copy(text: string): boolean;
    }
  }

  class VueClipboard {
    static install: PluginFunction<never>
  }
}

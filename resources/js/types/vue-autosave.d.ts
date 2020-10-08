declare module 'vue-autosave' {
  import Vue, { PluginFunction } from 'vue'

  module "vue/types/vue" {
    interface Vue {
      $saveDraft(key: string, value: string): void;
      $loadDraft(key: string): string;
      $removeDraft(key: string): void;
    }
  }

  class VueAutosave {
    static install: PluginFunction<never>
  }
}

declare module 'vue-autosave' {
  import Vue, { PluginFunction } from 'vue'

  module "vue/types/vue" {
    interface Vue {
      $saveDraft(data: string): void;
      $loadDraft(): string | void;
      $removeDraft(): void;
    }
  }

  class VueAutosave {
    static install: PluginFunction<never>
  }
}

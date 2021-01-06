<template>
  <div>
    <vue-markdown
      v-model="microblog.text"
      :assets.sync="microblog.assets"
      :auto-insert-assets="false"
      @save="saveMicroblog"
      @cancel="cancel"
      ref="markdown"
      preview-url="/Mikroblogi/Preview"
    >
      <template v-slot:bottom>
        <div class="row no-gutters p-1">
          <vue-tags-inline
            :tags="microblog.tags"
            placeholder="...inny? kliknij, aby wybrać tag"
            @change="toggleTag"
          ></vue-tags-inline>
        </div>
      </template>
    </vue-markdown>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" @click.native.prevent="saveMicroblog" title="Kliknij, aby wysłać (Ctrl+Enter)" class="btn btn-sm btn-primary float-right" tabindex="2" type="submit">
          Zapisz
        </vue-button>

        <button v-if="microblog.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-cancel btn-danger float-right mr-2" tabindex="3">
          Anuluj
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Component from "vue-class-component";
  import {Mixins, Prop, ProvideReactive, Ref} from "vue-property-decorator";
  import store from "../../store";
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VueMarkdown from '../forms/markdown.vue';
  import VueTagsInline from '../forms/tags-inline.vue';
  import { MicroblogFormMixin } from '../mixins/microblog';
  import {Tag} from "@/types/models";

  const DRAFT_KEY = 'microblog';

  @Component({
    name: 'microblog-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-markdown': VueMarkdown,
      'vue-tags-inline': VueTagsInline
    }
  })
  export default class VueForm extends Mixins(MicroblogFormMixin) {
    @Ref('markdown')
    public markdown!: VueMarkdown;

    @Prop({default: () => []})
    @ProvideReactive('popularTags')
    readonly popularTags!: string[];

    created() {
      if (this.microblog.id) {
        return;
      }

      this.$set(this.microblog, 'text', this.$loadDraft(DRAFT_KEY));
      this.$watch('microblog.text', newValue => this.$saveDraft(DRAFT_KEY, newValue));
    }

    saveMicroblog() {
      this.save('microblogs/save').then(() => this.$removeDraft(DRAFT_KEY))
    }

    toggleTag(tag: Tag) {
      store.commit('microblogs/toggleTag', { microblog: this.microblog, tag });
    }
  }
</script>



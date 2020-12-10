<template>
  <div>
    <vue-markdown
      v-model="microblog.text"
      :assets.sync="microblog.assets"
      @save="saveMicroblog"
      @cancel="cancel"
      ref="markdown"
      preview-url="/Mikroblogi/Preview"
    ></vue-markdown>

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
  import { Mixins, Ref } from "vue-property-decorator";
  import store from "../../store";
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VueMarkdown from '../forms/markdown.vue';
  import { MicroblogFormMixin } from '../mixins/microblog';

  @Component({
    name: 'microblog-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-markdown': VueMarkdown
    }
  })
  export default class VueForm extends Mixins(MicroblogFormMixin) {
    @Ref('markdown')
    public markdown!: VueMarkdown;

    saveMicroblog() {
      this.save('microblogs/save');
    }
  }
</script>



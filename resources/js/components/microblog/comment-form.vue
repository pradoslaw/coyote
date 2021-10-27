<template>
  <form>
    <vue-prompt>
      <textarea
        v-autosize
        v-paste:success="addAsset"
        placeholder="Napisz komentarz... (Ctrl+Enter aby wysłać)"
        class="form-control"
        name="text"
        ref="textarea"
        rows="1"
        v-model="microblog.text"
        :disabled="isProcessing"
        @keydown.ctrl.enter="saveComment"
        @keydown.meta.enter="saveComment"
        @keydown.esc="cancel"
      ></textarea>

      <button type="submit" @click.prevent="saveComment" class="btn btn-sm btn-comment-submit" title="Zapisz (Ctrl+Enter)"><i class="far fa-fw fa-share-square"></i></button>
    </vue-prompt>
  </form>
</template>

<script lang="ts">
  import Component from "vue-class-component";
  import { Mixins, Ref } from "vue-property-decorator";
  import store from "@/store";
  import VuePrompt from '../forms/prompt.vue';
  import { MicroblogFormMixin } from '../mixins/microblog';
  import { Asset } from "@/types/models";
  import { default as Textarea } from '@/libs/textarea';
  import IsImage from "@/libs/assets";

  @Component({
    name: 'microblog-comment-form',
    store,
    components: {
      'vue-prompt': VuePrompt
    }
  })
  export default class VueCommentForm extends Mixins(MicroblogFormMixin) {
    @Ref()
    readonly textarea!: HTMLTextAreaElement;

    saveComment() {
      this.save('microblogs/saveComment');
    }

    addAsset(asset: Asset) {
      this.microblog.assets.push(asset);
      this.insertAssetAtCaret(asset);
    }

    private insertAssetAtCaret(asset: Asset) {
      new Textarea(this.textarea).insertAtCaret((IsImage(asset.name!) ? '!' : '') + '[' + asset.name + '](' + asset.url + ')', '', '');

      this.textarea.dispatchEvent(new Event('input', {'bubbles': true}));
    }
  }
</script>

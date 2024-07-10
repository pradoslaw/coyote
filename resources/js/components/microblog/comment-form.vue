<template>
  <form>
    <vue-prompt>
      <textarea
        v-autosize
        v-paste:success="addAsset"
        placeholder="Napisz komentarz... (Ctrl+Enter aby wysłać)"
        class="form-control"
        name="text"
        ref="markdown"
        rows="1"
        v-model="microblog.text"
        :disabled="isProcessing"
        @keydown.ctrl.enter="saveComment"
        @keydown.meta.enter="saveComment"
        @keydown.esc="cancel"
      ></textarea>

      <button type="submit" @click.prevent="saveComment" class="btn btn-sm btn-comment-submit" title="Zapisz (Ctrl+Enter)"><i class="far fa-fw fa-share-from-square"></i></button>
    </vue-prompt>
  </form>
</template>

<script lang="ts">
import Vue from "vue";
import IsImage from "@/libs/assets";
import Textarea from '@/libs/textarea';
import store from "@/store";
import VuePrompt from '../forms/prompt.vue';
import {MicroblogFormMixin} from '../mixins/microblog';

export default Vue.extend({
  name: 'microblog-comment-form',
  store,
  components: {
    'vue-prompt': VuePrompt,
  },
  mixins: [MicroblogFormMixin],
  data() {
    return {
      markdown: null,
    };
  },
  mounted() {
    this.markdown = this.$refs.markdown;
  },
  methods: {
    saveComment() {
      this.save('microblogs/saveComment');
    },
    addAsset(asset) {
      this.microblog.assets.push(asset);
      this.insertAssetAtCaret(asset);
    },
    insertAssetAtCaret(asset) {
      new Textarea(this.markdown).insertAtCaret((IsImage(asset.name) ? '!' : '') + '[' + asset.name + '](' + asset.url + ')', '', '');
      this.markdown.dispatchEvent(new Event('input', {'bubbles': true}));
    },
  },
});
</script>

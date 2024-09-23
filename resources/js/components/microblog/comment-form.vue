<template>
  <form>
    <vue-comment-autocomplete
      source="/completion/prompt/users"
      placeholder="Napisz komentarz... (Ctrl+Enter aby wysłać)"
      v-model="microblog.text"

      allow-paste 
      @paste="addAsset"
      @save="saveComment"
      @cancel="cancel"

      ref="commentPrompt"
    >
      <button type="button" @click="saveComment" class="btn btn-sm btn-comment-submit" title="Zapisz (Ctrl+Enter)">
        <i class="far fa-fw fa-share-from-square"/>
      </button>
    </vue-comment-autocomplete>
  </form>
</template>

<script lang="ts">
import IsImage from "../../libs/assets";
import Textarea from '../../libs/textarea';
import store from "../../store/index";
import VueCommentAutocomplete from '../CommentAutocomplete.vue';
import {MicroblogFormMixin} from '../mixins/microblog';

export default {
  name: 'microblog-comment-form',
  store,
  components: {
    'vue-comment-autocomplete': VueCommentAutocomplete,
  },
  mixins: [MicroblogFormMixin],
  methods: {
    focus() {
      this.$refs.commentPrompt.focus();
    },
    saveComment() {
      this.save('microblogs/saveComment');
    },
    addAsset(asset) {
      this.microblog.assets.push(asset);
      this.insertAssetAtCaret(asset);
    },
    insertAssetAtCaret(asset) {
      this.$refs.commentPrompt.inspect(textarea => {
        new Textarea(textarea).insertAtCaret((IsImage(asset.name) ? '!' : '') + '[' + asset.name + '](' + asset.url + ')', '', '');
        textarea.dispatchEvent(new Event('input', {'bubbles': true}));
      });
    },
  },
};
</script>

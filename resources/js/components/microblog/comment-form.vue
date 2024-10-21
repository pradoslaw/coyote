<template>
  <form>
    <vue-comment-autocomplete
      source="/completion/prompt/users"
      :placeholder="editing 
          ? 'Edytujesz komentarz (Ctrl+Enter aby zapisać)' 
          : 'Napisz komentarz... (Ctrl+Enter aby wysłać)'"
      v-model="microblog.text"

      allow-paste
      @paste="addAsset"
      @save="saveComment"
      @cancel="cancel"

      ref="commentPrompt"
    >
      <button type="button" @click="saveComment" class="btn btn-sm btn-comment-submit" title="Zapisz (Ctrl+Enter)">
        <vue-icon v-if="editing" name="microblogCommentSaveExisting"/>
        <vue-icon v-else name="microblogCommentSaveNew"/>
      </button>
    </vue-comment-autocomplete>
  </form>
</template>

<script lang="ts">
import IsImage from "../../libs/assets";
import Textarea from '../../libs/textarea';
import store from "../../store/index";
import VueCommentAutocomplete from '../CommentAutocomplete.vue';
import VueIcon from "../icon";
import {MicroblogFormMixin} from '../mixins/microblog';

export default {
  name: 'microblog-comment-form',
  store,
  components: {
    VueIcon,
    'vue-comment-autocomplete': VueCommentAutocomplete,
  },
  props: {editing: {type: Boolean}},
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

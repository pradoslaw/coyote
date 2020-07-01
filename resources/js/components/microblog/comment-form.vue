<template>
  <form>
    <vue-prompt source="/User/Prompt">
      <textarea
        v-autosize
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
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Mixins } from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import { MicroblogFormMixin } from '../mixins/microblog';

  Vue.use(VueAutosize);

  @Component({
    name: 'microblog-comment-form',
    store,
    components: {
      'vue-prompt': VuePrompt
    }
  })
  export default class VueCommentForm extends Mixins(MicroblogFormMixin) {
    saveComment() {
      this.save('microblogs/saveComment');
    }
  }
</script>

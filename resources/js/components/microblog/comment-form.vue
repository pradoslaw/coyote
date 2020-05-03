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
        @keydown.esc="cancel"
      ></textarea>

      <button type="submit" @click.prevent="saveComment" class="btn btn-sm btn-comment-submit" title="Zapisz (Ctrl+Enter)"><i class="far fa-fw fa-share-square"></i></button>
    </vue-prompt>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop, Emit, Ref } from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import { Microblog } from "../../types/models";

  Vue.use(VueAutosize);

  @Component({
    name: 'microblog-comment-form',
    store,
    components: {
      'vue-prompt': VuePrompt
    }
  })
  export default class VueCommentForm extends Vue {
    protected isProcessing = false;

    @Prop({default() {
      return {}
    }})
    microblog!: Microblog;

    @Ref()
    readonly textarea!: HTMLTextAreaElement;

    @Emit()
    cancel() {
      //
    }

    saveComment() {
      this.isProcessing = true;

      store.dispatch('microblogs/saveComment', this.microblog)
        .then(() => {
          this.$emit('save');

          if (!this.microblog.id) {
            this.microblog.text = '';
          }
        })
        .finally(() => this.isProcessing = false);
    }
  }
</script>

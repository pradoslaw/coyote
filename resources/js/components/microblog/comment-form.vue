<template>
  <form>
    <vue-prompt source="/User/Prompt">
      <textarea-autosize
        placeholder="Napisz komentarz... (Ctrl+Enter aby wysłać)"
        class="form-control"
        name="text"
        ref="textarea"
        v-model="microblog.text"
        :min-height="25"
        :max-height="350"
        :disabled="isProcessing"
        @keydown.native.ctrl.enter="saveComment"
        @keypress.esc="cancel"
        rows="2"
      ></textarea-autosize>
    </vue-prompt>

    <button type="submit" @click.prevent="saveComment" class="btn btn-sm btn-comment-submit" title="Zapisz (Ctrl+Enter)"><i class="far fa-fw fa-share-square"></i></button>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop, Emit } from "vue-property-decorator";
  import store from "../../store";

  import VueTextareaAutosize from 'vue-textarea-autosize';
  import VuePrompt from '../forms/prompt.vue';

  import { mapActions, mapGetters } from "vuex";
  import { Microblog } from "../../types/models";

  Vue.use(VueTextareaAutosize);

  @Component({
    name: 'microblog-comment-form',
    store,
    components: {
      'vue-prompt': VuePrompt
    }
  })
  export default class VueCommentForm extends Vue {
    protected isProcessing = false;

    @Prop({default: {}})
    microblog!: Microblog;

    @Emit()
    cancel() {
      //
    }

    saveComment() {
      this.isProcessing = true;

      store.dispatch('microblogs/saveComment', this.microblog)
        .then(() => this.$emit('save'))
        .finally(() => {
          this.isProcessing = false;

          if (!this.microblog.id) {
            this.microblog.text = '';
          }
        });
    }
  }
</script>

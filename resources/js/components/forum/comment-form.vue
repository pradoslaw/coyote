<template>
  <form>
    <vue-prompt :source="`/completion/prompt/users/${$store.getters['topics/topic'].id}`" class="mb-2">
      <textarea
        v-autosize
        placeholder="Drobne uwagi, sugestie. Nie odpowiadaj tutaj na pytania zawarte w poście."
        class="form-control"
        name="text"
        ref="textarea"
        rows="1"
        :maxlength="maxLength"
        v-model="comment.text"
        :disabled="isProcessing"
        @keydown.ctrl.enter="saveComment"
        @keydown.meta.enter="saveComment"
        @keydown.esc="cancel"
      ></textarea>
    </vue-prompt>

    <div class="row">
      <div class="col-12">
        <p class="text-muted float-left">Pozostało <strong>{{ maxLength - comment.text.length }}</strong> znaków</p>

        <vue-button :disabled="isProcessing" @click.native.prevent="saveComment" class="btn btn-sm btn-primary float-right" title="Kliknij, aby wysłać (Ctrl+Enter)">Zapisz</vue-button>
        <button @click.prevent="cancel" class="btn btn-sm btn-danger float-right mr-2">Anuluj</button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import {Mixins, Prop, Ref} from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import { PostComment } from "../../types/models";

  Vue.use(VueAutosize);

  @Component({
    name: 'post-comment-form',
    store,
    components: {
      'vue-prompt': VuePrompt,
      'vue-button': VueButton
    }
  })
  export default class VueCommentForm extends Vue {
    @Prop(Object)
    comment!: PostComment;

    @Ref()
    readonly textarea!: HTMLTextAreaElement;

    private isProcessing = false;
    private readonly maxLength = 580;

    saveComment() {
      this.isProcessing = true;

      store.dispatch('posts/saveComment', this.comment)
        .then(() => {
          this.$emit('save');

          if (!this.comment.id) {
            this.comment.text = '';
          }
        })
        .finally(() => this.isProcessing = false);
    }

    cancel() {
      this.$emit('cancel');
    }
  }
</script>

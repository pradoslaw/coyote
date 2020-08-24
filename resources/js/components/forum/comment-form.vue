<template>
  <form>
    <vue-prompt source="/User/Prompt" class="mb-2">
      <textarea
        v-autosize
        placeholder="Drobne uwagi, sugestie. Nie odpowiadaj tutaj na pytania zawarte w poście."
        class="form-control"
        name="text"
        ref="textarea"
        rows="1"
        v-model="comment.text"
        :disabled="isProcessing"
        @keydown.ctrl.enter="saveComment"
        @keydown.meta.enter="saveComment"
        @keydown.esc="cancel"
      ></textarea>
    </vue-prompt>

    <p class="text-muted float-left">Pozostało <strong>580</strong> znaków</p>

    <button type="submit" class="btn btn-sm btn-primary float-right" title="Kliknij, aby wysłać (Ctrl+Enter)">Zapisz</button>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import {Mixins, Prop} from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import { PostComment } from "../../types/models";

  Vue.use(VueAutosize);

  @Component({
    name: 'post-comment-form',
    store,
    components: {
      'vue-prompt': VuePrompt
    }
  })
  export default class VueCommentForm extends Vue {
    @Prop(Object)
    comment!: PostComment;

    isProcessing = false;

    saveComment() {

    }

    cancel() {

    }
  }
</script>

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
        <p class="text-muted float-start">
          Pozostało <strong>{{ maxLength - comment.text.length }}</strong> znaków
        </p>
        <vue-button :disabled="isProcessing" @click="saveComment" class="btn btn-sm btn-primary float-end" title="Kliknij, aby wysłać (Ctrl+Enter)">
          <template v-if="newComment">Komentuj</template>
          <template v-else>Zapisz</template>
        </vue-button>
        <button @click.prevent="cancel" class="btn btn-sm btn-danger float-end me-2">Anuluj</button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import Vue from 'vue';

import VueAutosize from '../../plugins/autosize.js';
import store from "../../store/index";
import {PostComment} from "../../types/models";
import VueButton from '../forms/button.vue';
import VuePrompt from '../forms/prompt.vue';

Vue.use(VueAutosize);

export default Vue.extend({
  name: 'post-comment-form',
  props: {
    comment: {
      type: Object as () => PostComment,
      required: true,
    },
  },
  components: {
    'vue-prompt': VuePrompt,
    'vue-button': VueButton,
  },
  data() {
    return {
      isProcessing: false,
      maxLength: 580,
    };
  },
  methods: {
    saveComment() {
      this.$data.isProcessing = true;

      store.dispatch('posts/saveComment', this.comment)
        .then(() => {
          this.$emit('save');

          if (!this.comment.id) {
            this.comment.text = '';
          }
        })
        .finally(() => this.isProcessing = false);
    },
    focus() {
      (this.$refs.textarea as HTMLTextAreaElement).focus();
    },
    cancel() {
      this.$emit('cancel');
    },
  },
  computed: {
    newComment(): boolean {
      return typeof this.comment.id === 'undefined';
    },
  },
});
</script>

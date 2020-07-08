<template>
  <div>
    <div v-if="showTitleInput" class="form-group row">
      <label class="col-md-2 col-form-label text-right">Temat <em>*</em></label>

      <div class="col-md-10">
        <input v-model="topic.subject" tabindex="1" autofocus="autofocus" class="form-control" name="subject" type="text">
      </div>
    </div>

    <ul class="nav nav-tabs">
      <li class="nav-item"><a class="nav-link active">Treść</a></li>
      <li class="nav-item"><a class="nav-link">Załączniki</a></li>
      <li class="nav-item"><a class="nav-link">Podgląd</a></li>
    </ul>

    <vue-toolbar :input="() => $refs.textarea"></vue-toolbar>

    <vue-prompt source="/User/Prompt">
      <textarea
        v-autosize
        v-model="post.text"
        @keydown.ctrl.enter="save"
        @keydown.meta.enter="save"
        @keydown.esc="cancel"
        name="text"
        class="form-control"
        ref="textarea"
        rows="2"
        tabindex="1"
      ></textarea>
    </vue-prompt>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" title="Kliknij, aby zapisać (Ctrl+Enter)" class="btn btn-primary btn-sm float-right" @click.native.prevent="save">
          Zapisz
        </vue-button>

        <button v-if="post.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-danger float-right mr-2" tabindex="3">
          Anuluj
        </button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import {Ref, Mixins, Prop, Emit} from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VuePaste from '../../plugins/paste.js';
  import VueToolbar from '../../components/forms/toolbar.vue';
  import { Post, Topic } from "../../types/models";

  Vue.use(VueAutosize);
  Vue.use(VuePaste, {url: '/Mikroblogi/Paste'});

  @Component({
    name: 'forum-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-toolbar': VueToolbar
    }
  })
  export default class VueForm extends Vue {
    isProcessing = false;

    @Ref()
    readonly textarea!: HTMLTextAreaElement;

    @Prop({default: false})
    readonly showTitleInput!: boolean;

    @Prop({default() {
      return {
        text: ''
      }
    }})
    readonly post!: Post;

    @Prop({default() {
      return {
        subject: ''
      }
    }})
    readonly topic!: Topic;

    @Emit()
    cancel() { }

    save() {
      this.isProcessing = true;

      store.dispatch('posts/save', { post: this.post, topic: this.topic })
        .then(result => {
          this.$emit('save', result.data);

          if (!this.post.id) {
            this.post.text = '';
          }
        })
        .finally(() => this.isProcessing = false);
    }

  }
</script>

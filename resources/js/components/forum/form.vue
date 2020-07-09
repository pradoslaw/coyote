<template>
  <div>
    <div v-if="showTitleInput" class="form-group">
      <label class="col-form-label">Temat <em>*</em></label>

      <input v-model="topic.subject" tabindex="1" autofocus="autofocus" class="form-control" name="subject" type="text">

      <small class="text-muted form-text">Bądź rzeczowy. Nie nadawaj wątkom jednowyrazowych tematów.</small>
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

    <div v-if="showTagsInput" class="form-group">
      <label class="col-form-label">Tagi <em>*</em></label>

      <input class="form-control" name="tags" type="text">
    </div>

    <div v-if="showStickyCheckbox" class="form-group">
      <div class="custom-control custom-checkbox">
        <input v-model="topic.is_sticky" type="checkbox" class="custom-control-input" id="is-sticky">
        <label class="custom-control-label" for="is-sticky">Przyklejony wątek</label>
      </div>
    </div>

    <div v-if="showSubscribeCheckbox" class="form-group">
      <div class="custom-control custom-checkbox">
        <input v-model="topic.is_subscribed" type="checkbox" class="custom-control-input" id="is-subscribed">
        <label class="custom-control-label" for="is-subscribed">Obserwowany wątek</label>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" title="Kliknij, aby zapisać (Ctrl+Enter)" class="btn btn-primary btn-sm" @click.native.prevent="save">
          Zapisz
        </vue-button>

        <button v-if="post.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-danger mr-2" tabindex="3">
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
  import { mapActions, mapGetters, mapState } from "vuex";

  Vue.use(VueAutosize);
  Vue.use(VuePaste, {url: '/Mikroblogi/Paste'});

  @Component({
    name: 'forum-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-toolbar': VueToolbar
    },
    computed: {
      ...mapState('posts', ['topic'])
    }
  })
  export default class VueForm extends Vue {
    isProcessing = false;

    @Ref()
    readonly textarea!: HTMLTextAreaElement;

    @Prop({default: false})
    readonly showTitleInput!: boolean;

    @Prop({default: false})
    readonly showTagsInput!: boolean;

    @Prop({default: false})
    readonly showStickyCheckbox!: boolean;

    @Prop({default: false})
    readonly showSubscribeCheckbox!: boolean;

    @Prop({default() {
      return {
        text: ''
      }
    }})
    readonly post!: Post;

    // @Prop({default() {
    //   return {
    //     subject: ''
    //   }
    // }})
    public topic!: Topic;

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

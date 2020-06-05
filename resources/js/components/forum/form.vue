<template>
  <div>
    <ul class="nav nav-tabs">
      <li class="nav-item"><a class="nav-link active">Treść</a></li>
      <li class="nav-item"><a class="nav-link">Załączniki</a></li>
      <li class="nav-item"><a class="nav-link">Podgląd</a></li>
    </ul>

    <vue-toolbar :input="() => $refs.textarea"></vue-toolbar>

    <vue-prompt source="/User/Prompt">
      <textarea
        v-autosize
        name="text"
        class="form-control"
        ref="textarea"
        rows="2"
        tabindex="1"
      ></textarea>
    </vue-prompt>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" class="btn btn-primary btn-sm float-right">Zapisz</vue-button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Ref, Mixins } from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VuePaste from '../../plugins/paste.js';
  import VueToolbar from '../../components/forms/toolbar.vue';
  import { Post } from "../../types/models";

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

    @Ref('post')
    readonly post!: Post;

  }
</script>

<template>
  <div class="microblog-submit">

    <vue-prompt source="/User/Prompt">
      <textarea
        v-autosize
        placeholder="Kliknij, aby dodać wpis"
        v-clipboard:success="addImage"
        v-clipboard:error="showError"
        name="text"
        ref="textarea"
        v-model="microblog.text"
        @keydown.ctrl.enter="save"
        @keydown.esc="cancel"
        rows="2"
        tabindex="1"
      ></textarea>
    </vue-prompt>

    <div class="row submit-area">
      <div class="col-12">
        <div class="row thumbnails">
          <div v-for="media in microblog.media" class="col-sm-2">
<!--          <div v-for="media in microblog.media" v-show="media.url" class="col-sm-2">-->
            <vue-thumbnail
              ref="thumbnail"
              :url="media.url"
              :file="media.name"
              upload-url="/Mikroblogi/Upload"
              @upload="addImage"
              @delete="deleteImage">
            </vue-thumbnail>
          </div>
        </div>

        <div class="row">
          <div class="col-6">
            <button @click="addEmptyImage" title="Kliknij, aby dodać zdjęcie" class="btn btn-secondary btn-sm" type="button">
              <i class="fas fa-camera"></i>
            </button>
          </div>
          <div class="col-6">
            <vue-button :disabled="isProcessing" @click.native.prevent="save" title="Kliknij, aby wysłać (Ctrl+Enter)" class="btn btn-sm btn-primary float-right" tabindex="2" type="submit">
              Zapisz
            </vue-button>

            <button v-if="microblog.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-cancel btn-danger float-right" style="margin-right: 10px" tabindex="3">
              Anuluj
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import {Prop, Emit, Ref} from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VueClipboard from '../../plugins/clipboard.js';
  import VueThumbnail from '../thumbnail.vue';
  import { Microblog } from "../../types/models";

  Vue.use(VueAutosize);
  Vue.use(VueClipboard, {url: '/Mikroblogi/Paste'});

  @Component({
    name: 'microblog-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-thumbnail': VueThumbnail
    }
  })
  export default class VueForm extends Vue {
    isProcessing = false;

    @Prop({default() {
        return {
          media: []
        }
    }})
    microblog!: Microblog;

    @Ref('textarea')
    readonly textarea!: HTMLTextAreaElement;

    @Ref('thumbnail')
    readonly thumbnail!: VueThumbnail[];

    @Emit()
    cancel() {
      //
    }

    save() {
      this.isProcessing = true;

      store.dispatch('microblogs/save', this.microblog)
        .then(() => this.$emit('save'))
        .finally(() => {
          this.isProcessing = false;

          if (!this.microblog.id) {
            this.microblog.text = '';
          }
        });
    }

    addEmptyImage() {
      if (!this.microblog.media.length || !this.microblog.media[this.microblog.media.length - 1].url) {
        store.commit('microblogs/addEmptyImage', this.microblog);
      }

      // @ts-ignore
      this.$nextTick(() => this.thumbnail[this.thumbnail.length - 1].openDialog());

    }

    addImage(media) {
      store.commit('microblogs/deleteImage', { microblog: this.microblog, media: '' });
      store.commit('microblogs/addImage', { microblog: this.microblog, media })
    }

    deleteImage(name) {

    }

    showError() {

    }
  }
</script>



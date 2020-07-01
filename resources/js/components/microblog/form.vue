<template>
  <div class="microblog-submit">

    <vue-prompt source="/User/Prompt" class="border-bottom">
      <textarea
        v-autosize
        placeholder="Kliknij, aby dodać wpis"
        v-paste:success="addImage"
        name="text"
        ref="textarea"
        v-model="microblog.text"
        @keydown.ctrl.enter="saveMicroblog"
        @keydown.meta.enter="saveMicroblog"
        @keydown.esc="cancel"
        rows="2"
        tabindex="1"
      ></textarea>
    </vue-prompt>

    <div class="row pt-3 pb-3">
      <div v-for="media in microblog.media" v-show="media.url" class="col-sm-2">
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
        <vue-button :disabled="isProcessing" @click.native.prevent="saveMicroblog" title="Kliknij, aby wysłać (Ctrl+Enter)" class="btn btn-sm btn-primary float-right" tabindex="2" type="submit">
          Zapisz
        </vue-button>

        <button v-if="microblog.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-cancel btn-danger float-right mr-2" tabindex="3">
          Anuluj
        </button>
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
  import VueThumbnail from '../thumbnail.vue';
  import { MicroblogFormMixin } from '../mixins/microblog';

  Vue.use(VueAutosize);
  Vue.use(VuePaste, {url: '/Mikroblogi/Paste'});

  @Component({
    name: 'microblog-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-thumbnail': VueThumbnail
    }
  })
  export default class VueForm extends Mixins(MicroblogFormMixin) {

    @Ref('thumbnail')
    readonly thumbnail!: VueThumbnail[];

    saveMicroblog() {
      this.save('microblogs/save');
    }

    addEmptyImage() {
      if (!this.microblog.media.length || !this.microblog.media[this.microblog.media.length - 1].url) {
        store.commit('microblogs/addImage', { microblog: this.microblog, media: {url: '', name: '', thumbnail: ''} });
      }

      // @ts-ignore
      this.$nextTick(() => this.thumbnail[this.thumbnail.length - 1].openDialog());
    }

    addImage(media) {
      store.commit('microblogs/addImage', { microblog: this.microblog, media })
    }

    deleteImage(name) {
      store.commit('microblogs/deleteImage', { microblog: this.microblog, media: name });
    }
  }
</script>



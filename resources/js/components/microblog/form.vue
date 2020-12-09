<template>
  <div>

    <vue-markdown v-model="microblog.text" :media.sync="microblog.media"></vue-markdown>

<!--    <vue-prompt class="border-bottom">-->
<!--      <textarea-->
<!--        v-autosize-->
<!--        placeholder="Kliknij, aby dodać wpis"-->
<!--        v-paste:success="addImage"-->
<!--        name="text"-->
<!--        ref="textarea"-->
<!--        v-model="microblog.text"-->
<!--        @keydown.ctrl.enter="saveMicroblog"-->
<!--        @keydown.meta.enter="saveMicroblog"-->
<!--        @keydown.esc="cancel"-->
<!--        rows="2"-->
<!--        tabindex="1"-->
<!--      ></textarea>-->
<!--    </vue-prompt>-->

<!--    <div class="row pt-3 pb-3">-->
<!--      <div v-for="media in microblog.media" v-show="media.url" class="col-sm-2">-->
<!--        <vue-thumbnail-->
<!--          ref="thumbnail"-->
<!--          :url="media.url"-->
<!--          upload-url="/Mikroblogi/Upload"-->
<!--          @upload="addImage"-->
<!--          @delete="deleteImage">-->
<!--        </vue-thumbnail>-->
<!--      </div>-->
<!--    </div>-->

    <div class="row mt-2">
<!--      <div class="col-6">-->
<!--        <button @click="addEmptyImage" title="Kliknij, aby dodać zdjęcie" class="btn btn-secondary btn-sm" type="button">-->
<!--          <i class="fas fa-camera"></i>-->
<!--        </button>-->
<!--      </div>-->
      <div class="col-12">
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
  import Component from "vue-class-component";
  import { Mixins, Watch } from "vue-property-decorator";
  import store from "../../store";
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VueMarkdown from '../forms/markdown.vue';
  import { MicroblogFormMixin } from '../mixins/microblog';

  @Component({
    name: 'microblog-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-markdown': VueMarkdown
    }
  })
  export default class VueForm extends Mixins(MicroblogFormMixin) {

    saveMicroblog() {
      this.save('microblogs/save');
    }

    // @Watch('microblog.media')
    // onMediaChange(path) {
    //   console.log(path);
    // }

    // addImage(media) {
    //   store.commit('microblogs/addImage', { microblog: this.microblog, media })
    // }
    //
    // deleteImage(url) {
    //   store.commit('microblogs/deleteImage', { microblog: this.microblog, media: url });
    // }
  }
</script>



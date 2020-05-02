<template>
  <div class="microblog-submit">

    <vue-prompt source="/User/Prompt" :errors="errors.text">
      <textarea
        v-autosize
        placeholder="Kliknij, aby dodać wpis"
        v-clipboard:success="addThumbnail"
        v-clipboard:error="showError"
        name="text"
        ref="textarea"
        v-model="microblog.text"
        @keydown.ctrl.enter="save"
        rows="2"
        tabindex="1"
      ></textarea>
    </vue-prompt>

    <div class="row submit-area">
      <div class="col-12">
        <div class="row thumbnails">
          <!--                  {% for media in microblog.media %}-->
          <!--                  <div class="col-sm-2">-->
          <!--                    <a href="javascript:" class="thumbnail">-->
          <!--                      <img src="{{ thumbnail(media) }}">-->

          <!--                      <div class="btn-flush">-->
          <!--                        <i class="fas fa-times fa-2x"></i>-->
          <!--                        <input type="hidden" name="thumbnail[]" value="{{ media }}">-->
          <!--                      </div>-->
          <!--                    </a>-->
          <!--                  </div>-->
          <!--                  {% endfor %}-->
        </div>

        <div class="row">
          <div class="col-6">
            <button title="Kliknij, aby dodać zdjęcie" class="btn btn-secondary btn-sm" type="button" id="btn-upload">
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
  import { Microblog } from "../../types/models";

  Vue.use(VueAutosize);
  Vue.use(VueClipboard, {url: '/Mikroblogi/Upload'});

  @Component({
    name: 'microblog-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt
    }
  })
  export default class VueForm extends Vue {
    errors = {};
    isProcessing = false;

    @Prop({default() {
        return {}
    }})
    microblog!: Microblog;

    @Ref('textarea')
    readonly textarea!: HTMLTextAreaElement;

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

    addThumbnail() {

    }

    showError() {

    }
  }
</script>



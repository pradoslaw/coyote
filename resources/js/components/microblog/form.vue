<template>
  <form @submit.prevent="saveMicroblog">
    <vue-markdown
      v-model="microblog.text"
      :assets.sync="microblog.assets"
      :emojis="emojis"
      :auto-insert-assets="false"
      @save="saveMicroblog"
      @cancel="cancel"
      ref="markdown"
      preview-url="/Mikroblogi/Preview">
      <template v-slot:bottom>
        <div class="p-1">
          <vue-tags-inline
            :tags="microblog.tags"
            placeholder="...inny? kliknij, aby wybrać tag"
            @change="toggleTag"
            :popular-tags="popularTags"
          />
        </div>
      </template>
    </vue-markdown>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button
          :disabled="isProcessing"
          @click="saveMicroblog"
          title="Kliknij, aby wysłać (Ctrl+Enter)"
          class="btn btn-sm btn-primary float-end"
          tabindex="2"
          type="submit">
          Zapisz
        </vue-button>
        <button v-if="microblog.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-cancel btn-danger float-end me-2" tabindex="3">
          Anuluj
        </button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import axios from 'axios';
import {loadDraft, removeDraft, saveDraft} from "../../plugins/autosave";
import store from "../../store";
import {Tag} from "../../types/models";
import VueButton from '../forms/button.vue';
import VueMarkdown from '../forms/markdown.vue';
import VuePrompt from '../forms/prompt.vue';
import VueTagsInline from '../forms/tags-inline.vue';
import {MicroblogFormMixin} from '../mixins/microblog';

const DRAFT_KEY = 'microblog';

export default {
  name: 'microblog-form',
  store,
  components: {
    'vue-button': VueButton,
    'vue-prompt': VuePrompt,
    'vue-markdown': VueMarkdown,
    'vue-tags-inline': VueTagsInline,
  },
  mixins: [MicroblogFormMixin],
  props: {
    popularTags: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      timeoutId: undefined,
      urlDetector: null,
      cancelTokenSource: undefined,
      emojis: undefined,
    };
  },
  created() {
    this.emojis = window.emojis;
    if (this.microblog.id) {
      return;
    }
    this.$set(this.microblog, 'text', loadDraft(DRAFT_KEY));
    this.$watch('microblog.text', newValue => saveDraft(DRAFT_KEY, newValue));
    this.startUrlDetector();
  },
  methods: {
    detectUrl() {
      const handler = () => {
        const matches = this.microblog.text.match(/(\b(https?|ftp|file):\/\/[-A-Z0-9+&@#\/%?=~_|!:,.;]*[-A-Z0-9+&@#\/%=~_|])/ig);

        if (!matches) {
          return;
        }

        this.urlDetector(); // remove watcher

        this.cancelTokenSource = axios.CancelToken.source();

        axios.get<any>('/assets/opg', {params: {url: matches[0]}, errorHandle: false, cancelToken: this.cancelTokenSource.token})
          .then(response => this.microblog.assets.push(response.data))
          .catch(this.startUrlDetector);
      };

      clearTimeout(this.timeoutId);
      this.timeoutId = window.setTimeout(handler, 500);
    },
    saveMicroblog() {
      if (this.cancelTokenSource) {
        this.cancelTokenSource.cancel();
      }
      this.save('microblogs/save').then(() => removeDraft(DRAFT_KEY));
    },
    toggleTag(tag: Tag) {
      store.commit('microblogs/TOGGLE_TAG', {microblog: this.microblog, tag});
    },
    startUrlDetector() {
      this.urlDetector = this.$watch('microblog.text', this.detectUrl);
    },
  },
};
</script>

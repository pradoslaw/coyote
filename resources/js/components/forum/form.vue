<template>
  <form @submit.prevent="save">
    <div v-if="showTitleInput" class="form-group">
      <label class="col-form-label">Temat <em>*</em></label>

      <vue-text v-model="topic.title" :is-invalid="'title' in errors" @keydown.enter.native="save" @blur.native="findSimilar" name="title" tabindex="1" autofocus="autofocus"></vue-text>
      <vue-error :message="errors['title']"></vue-error>

      <small v-if="!('title' in errors)" class="text-muted form-text">Bądź rzeczowy. Nie nadawaj wątkom jednowyrazowych tytułów.</small>

      <div v-if="similar.length" class="card mt-2">
        <div class="card-header p-3">Podobne wątki</div>

        <div class="card-body related mt-0 p-3">
          <perfect-scrollbar tag="ul" class="position-relative" style="height: 100px">
            <li v-for="topic in similar">
              <a :href="topic.url">
                <strong>{{ topic.title }}</strong>
                <small>
                  <vue-timeago :datetime="topic.last_post_created_at"/>
                </small>
              </a>
            </li>
          </perfect-scrollbar>
        </div>
      </div>
    </div>

    <div class="form-group">
      <vue-markdown
        v-model="post.text"
        :prompt-url="`/completion/prompt/users/${topic.id || ''}`"
        :error="errors['text']"
        :assets.sync="post.assets"
        :emojis="emojis"
        preview-url="/Forum/Preview"
        @save="save"
        @cancel="cancel"
        ref="markdown">
        <template v-if="isFirstPost" v-slot:options>
          <a href="javascript:" data-bs-target="#js-poll-form" data-bs-toggle="collapse" class="ms-1 small text-muted">
            <i class="fa fa-square-poll-horizontal"/>
            <span class="d-none d-sm-inline">
              Ankieta
            </span>
          </a>
        </template>

        <div v-if="isFirstPost" id="js-poll-form" class="bg-light p-3 mt-2 collapse">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-end">Odpowiedzi w ankiecie</label>

            <div class="col-md-6">
              <div v-for="(item, index) in poll.items" :key="item.id" class="input-group mb-1">
                <a @click="removeItem(item)" class="input-group-text text-decoration-none" href="javascript:">
                  <i :class="{'text-danger': poll.items.length > 2, 'text-muted': poll.items.length <= 2}" title="Usuń odpowiedź" class="fas fa-fw fa-circle-minus"></i>
                </a>

                <vue-text
                  v-model="item.text"
                  :is-invalid="`poll.items.${index}.text` in errors"
                  ref="poll-items"
                  class="input-sm"
                  @keydown.enter.native.prevent="addItem"
                  placeholder="Naciśnij Enter, aby dodać kolejną pozycję"/>

                <vue-error :message="errors[`poll.items.${index}.text`]"></vue-error>
              </div>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-3 col-form-label text-end">Możliwych odpowiedzi</label>

            <div class="col-md-6">
              <vue-text v-model="poll.max_items" :is-invalid="`poll.max_items` in errors" class="input-sm"></vue-text>
              <vue-error :message="errors['poll.max_items']"/>
              <span class="form-text text-muted">Minimalnie jedna możliwa odpowiedź w ankiecie.</span>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-3 col-form-label text-end">Długość działania</label>

            <div class="col-md-6">
              <vue-text v-model="poll.length" :is-invalid="`poll.length` in errors" class="input-sm"></vue-text>
              <vue-error :message="errors['poll.length']"></vue-error>

              <span class="form-text text-muted">Określ długość działania ankiety (w dniach). 0 oznacza brak terminu ważności.</span>
            </div>
          </div>

          <div v-if="poll.id" class="form-group row">
            <div class="col-md-6 offset-md-4">
              <button @click="resetDefaults" class="btn btn-danger btn-sm">
                Usuń ankietę
              </button>
            </div>
          </div>
        </div>

        <template v-slot:bottom>
          <div v-if="showTagsInput" class="p-1 d-flex">
            <vue-tags-inline
              :tags="topic.tags"
              :class="{'is-invalid': 'tags' in errors}"
              :popular-tags="popularTags"
              :placeholder="requireTag ? 'Minimum 1 tag jest wymagany': '...inny? kliknij, aby wybrać tag'"
              @change="toggleTag"/>
            <vue-error :message="errors['tags']"/>
          </div>
        </template>
      </vue-markdown>
    </div>

    <div v-if="showStickyCheckbox" class="form-group">
      <div class="custom-control custom-checkbox">
        <input v-model="topic.is_sticky" type="checkbox" class="custom-control-input" id="is-sticky">
        <label class="custom-control-label" for="is-sticky">Przyklejony wątek</label>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" title="Kliknij, aby zapisać (Ctrl+Enter)" class="btn btn-primary btn-sm" @click.native.prevent="save">
          Zapisz
        </vue-button>

        <button v-if="post.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-danger me-2">
          Anuluj
        </button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import axios from 'axios';
import Prism from 'prismjs';
import Vue from 'vue';
import {mapGetters, mapMutations, mapState} from "vuex";
import VueMarkdown from '../../components/forms/markdown.vue';
import store from "../../store";
import VueButton from '../forms/button.vue';
import VueError from '../forms/error.vue';
import VueTagsInline from '../forms/tags-inline.vue';
import VueText from '../forms/text.vue';
import PerfectScrollbar from '../perfect-scrollbar.js';

export default Vue.extend({
  name: 'forum-form',
  store,
  components: {
    'vue-button': VueButton,
    'vue-markdown': VueMarkdown,
    'vue-tags-inline': VueTagsInline,
    'vue-error': VueError,
    'vue-text': VueText,
    'perfect-scrollbar': PerfectScrollbar,
  },
  props: {
    showTitleInput: {
      type: Boolean,
      default: false,
    },
    showTagsInput: {
      type: Boolean,
      default: false,
    },
    showStickyCheckbox: {
      type: Boolean,
      default: false,
    },
    requireTag: {
      type: Boolean,
      default: false,
    },
    popularTags: {
      type: Array,
      default: () => [],
    },
    post: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isProcessing: false,
      currentTab: 0,
      errors: {},
      similar: [],
      emojis: window.emojis,
      originalText: this.post.text,
    };
  },
  watch: {
    poll: {
      handler(poll) {
        store.commit('poll/init', poll);
      },
      deep: true,
    },
  },
  created() {
    this.emojis = window.emojis;
    if (!this.exists) {
      this.post.text = this.$loadDraft(this.draftKey) as string;
      this.$watch('post.text', newValue => this.$saveDraft(this.draftKey, newValue));
    }
    this.originalText = this.post.text;
  },
  methods: {
    cancel() {
      this.post.text = this.originalText;
      this.$emit('cancel');
    },
    ...mapMutations('poll', ['removeItem', 'resetDefaults']),
    ...mapMutations('posts', ['deleteAttachment', 'changePage']),
    async save() {
      await this.validateTags();

      this.isProcessing = true;
      this.errors = {};

      await this.lastPage();

      store.dispatch('posts/save', this.post)
        .then(result => {
          this.$emit('save', result.data);

          this.$nextTick(() => {
            // remove local storage data after clearing input
            this.$removeDraft(this.draftKey);
            Prism.highlightAll();
          });
        })
        .catch(err => {
          if (err.response?.status !== 422) {
            return;
          }
          this.errors = err.response?.data.errors;
        })
        .finally(() => this.isProcessing = false);
    },
    openDialog() {
      (this.$refs.attachment as HTMLInputElement).click();
    },
    toggleTag(tag) {
      store.commit('topics/toggleTag', {topic: this.topic, tag});
    },
    findSimilar() {
      if (!this.topic.title) {
        return;
      }
      axios.get('/completion/similar', {params: {q: this.topic.title}}).then(response => this.similar = response.data.hits);
    },
    addItem() {
      store.commit('poll/addItem');

      // @ts-ignore
      this.$nextTick(() => this.$refs['poll-items'][this.$refs['poll-items'].length - 1].$el.focus());
    },
    async lastPage() {
      if (!this.exists && this.currentPage < this.totalPages) {
        history.pushState({page: this.totalPages}, '', `?page=${this.totalPages}`);

        await store.dispatch('posts/changePage', this.totalPages);
      }
    },
    async validateTags() {
      if (!this.topic.tags?.length || !this.isFirstPost) {
        return;
      }

      this.isProcessing = true;
      const response = await axios.post('/Forum/Tag/Validation', {tags: this.topic.tags.map(tag => tag.name)});

      this.isProcessing = false;

      if (!response.data.warning) {
        return;
      }

      await this.$confirm({message: response.data.message, title: 'Czy to tag techniczny?', okLabel: 'Tak, jestem pewien'});

      return true;
    },
  },
  computed: {
    ...mapGetters('topics', ['topic']),
    ...mapState('poll', ['poll']),
    ...mapGetters('posts', ['totalPages', 'currentPage']),
    isFirstPost() {
      return !this.topic || this.topic.first_post_id === this.post.id;
    },
    draftKey() {
      return `topic-${this.topic.id ? this.topic.id : ''}`;
    },
    exists() {
      return this.post.id !== undefined;
    },
  },
});
</script>

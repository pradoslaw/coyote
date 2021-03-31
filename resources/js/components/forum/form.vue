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
                <small><vue-timeago :datetime="topic.last_post_created_at"></vue-timeago></small>
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
        preview-url="/Forum/Preview"
        @save="save"
        @cancel="cancel"
        ref="markdown"
      >
        <template v-if="isFirstPost" v-slot:options>
          <a href="javascript:" data-target="#js-poll-form" data-toggle="collapse" class="ml-1 small text-muted">
            <i class="fa fa-poll-h"></i>

            <span class="d-none d-sm-inline">Ankieta</span>
          </a>
        </template>

        <div v-if="isFirstPost" id="js-poll-form" class="bg-light p-3 mt-2 collapse">
          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Odpowiedzi w ankiecie</label>

            <div class="col-md-6">
              <div v-for="(item, index) in poll.items" :key="item.id" class="input-group mb-1">
                <div class="input-group-prepend">
                  <a @click="removeItem(item)" class="input-group-text text-decoration-none" href="javascript:">
                    <i :class="{'text-danger': poll.items.length > 2, 'text-muted': poll.items.length <= 2}" title="Usuń odpowiedź" class="fas fa-fw fa-minus-circle"></i>
                  </a>
                </div>

                <vue-text
                  v-model="item.text"
                  :is-invalid="`poll.items.${index}.text` in errors"
                  class="input-sm"
                  @keydown.enter.native.prevent="addItem"
                  placeholder="Naciśnij Enter, aby dodać kolejną pozycję"
                ></vue-text>

                <vue-error :message="errors[`poll.items.${index}.text`]"></vue-error>
              </div>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Możliwych odpowiedzi</label>

            <div class="col-md-6">
              <vue-text v-model="poll.max_items" :is-invalid="`poll.max_items` in errors" class="input-sm"></vue-text>
              <vue-error :message="errors['poll.max_items']"></vue-error>

              <span class="form-text text-muted">Minimalnie jedna możliwa odpowiedź w ankiecie.</span>
            </div>
          </div>

          <div class="form-group row">
            <label class="col-md-3 col-form-label text-right">Długość działania</label>

            <div class="col-md-6">
              <vue-text v-model="poll.length" :is-invalid="`poll.length` in errors" class="input-sm"></vue-text>
              <vue-error :message="errors['poll.length']"></vue-error>

              <span class="form-text text-muted">Określ długość działania ankiety (w dniach). 0 oznacza brak terminu ważności.</span>
            </div>
          </div>

          <div v-if="poll.id" class="form-group row">
            <div class="col-md-6 offset-md-4">
              <button @click="resetDefaults" class="btn btn-danger btn-sm">Usuń ankietę</button>
            </div>
          </div>
        </div>

        <template v-slot:bottom>
          <div v-if="showTagsInput" class="row no-gutters p-1">
            <vue-tags-inline
              :tags="topic.tags"
              :class="{'is-invalid': 'tags' in errors}"
              :placeholder="requireTag ? 'Minimum 1 tag jest wymagany': '...inny? kliknij, aby wybrać tag'"
              @change="toggleTag"
            ></vue-tags-inline>

            <vue-error :message="errors['tags']"></vue-error>
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

        <button v-if="post.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-danger mr-2">
          Anuluj
        </button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import {Ref, Prop, Emit, ProvideReactive} from "vue-property-decorator";
  import store from "../../store";
  import VueButton from '../forms/button.vue';
  import VueTagsInline from '../forms/tags-inline.vue';
  import VueMarkdown from '../../components/forms/markdown.vue';
  import { Post, Topic, Tag } from "@/types/models";
  import { mapMutations, mapState, mapGetters } from "vuex";
  import axios from 'axios';
  import VueError from '../forms/error.vue';
  import VueText from '../forms/text.vue';
  import Prism from 'prismjs';
  import {default as PerfectScrollbar} from '../perfect-scrollbar';

  @Component({
    name: 'forum-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-markdown': VueMarkdown,
      'vue-tags-inline': VueTagsInline,
      'vue-error': VueError,
      'vue-text': VueText,
      'perfect-scrollbar': PerfectScrollbar
    },
    computed: {
      ...mapGetters('topics', ['topic']),
      ...mapState('poll', ['poll']),
      ...mapGetters('posts', ['totalPages', 'currentPage'])
    },
    watch: {
      poll: {
        handler(poll) {
          store.commit('poll/init', poll);
        },
        deep: true
      }
    },
    methods: {
      ...mapMutations('poll', ['removeItem', 'addItem', 'resetDefaults']),
      ...mapMutations('posts', ['deleteAttachment', 'changePage'])
    },
    inject: []
  })
  export default class VueForm extends Vue {
    isProcessing = false;
    currentTab: number = 0;
    errors = {};
    similar: Topic[] = [];
    readonly topic!: Topic;
    readonly totalPages!: number;
    readonly currentPage!: number;

    @Ref('markdown')
    readonly markdown!: VueMarkdown;

    @Ref('attachment')
    readonly attachment!: HTMLInputElement;

    @Prop({default: false})
    readonly showTitleInput!: boolean;

    @Prop({default: false})
    readonly showTagsInput!: boolean;

    @Prop({default: false})
    readonly showStickyCheckbox!: boolean;

    @Prop({default: false})
    readonly requireTag!: boolean;

    @Prop({default: () => []})
    @ProvideReactive('popularTags')
    readonly popularTags!: string[];

    @Prop({required: true})
    post!: Post;

    @Emit()
    cancel() { }

    created() {
      if (this.exists) {
        return;
      }

      this.post.text = this.$loadDraft(this.draftKey) as string;
      this.$watch('post.text', newValue => this.$saveDraft(this.draftKey, newValue));
    }

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
    }

    openDialog() {
      (this.$refs.attachment as HTMLInputElement).click();
    }

    toggleTag(tag: Tag) {
      store.commit('topics/toggleTag', { topic: this.topic, tag });
    }

    findSimilar() {
      if (!this.topic.title) {
        return;
      }

      axios.get('/completion/similar', { params: { q: this.topic.title }}).then(response => this.similar = response.data.hits);
    }

    async lastPage() {
      if (!this.exists && this.currentPage < this.totalPages) {
        history.pushState({ page: this.totalPages }, '', `?page=${this.totalPages}`);

        await store.dispatch('posts/changePage', this.totalPages);
      }
    }

    async validateTags() {
      if (!this.topic.tags?.length || !this.isFirstPost) {
        return;
      }

      this.isProcessing = true;
      const response = await axios.post('/Forum/Tag/Validation', { tags: this.topic.tags.map(tag => tag.name) });

      this.isProcessing = false;

      if (!response.data.warning) {
        return;
      }

      await this.$confirm({ message: response.data.message, title: 'Dodać nowy tag?', okLabel: 'Tak, jestem pewien' });

      return true;
    }

    private get isFirstPost() {
      return !this.topic || this.topic.first_post_id === this.post.id;
    }

    private get draftKey(): string {
      return `topic-${this.topic.id ? this.topic.id : ''}`
    }

    private get exists(): boolean {
      return this.post.id !== undefined;
    }
  }
</script>

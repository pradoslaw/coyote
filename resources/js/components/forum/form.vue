<template>
  <div>
    <div v-if="showTitleInput" class="form-group">
      <label class="col-form-label">Temat <em>*</em></label>

      <vue-text :value.sync="topic.subject" :is-invalid="'subject' in errors" @keydown.enter.native="save" name="subject" tabindex="1" autofocus="autofocus"></vue-text>
      <vue-error :message="errors['subject']"></vue-error>

      <small v-if="!('subject' in errors)" class="text-muted form-text">Bądź rzeczowy. Nie nadawaj wątkom jednowyrazowych tematów.</small>
    </div>

    <ul class="nav nav-tabs">
      <li class="nav-item"><a @click="switchTab('textarea')" :class="{active: activeTab === 'textarea'}" class="nav-link" href="javascript:">Treść</a></li>
      <li class="nav-item"><a @click="switchTab('attachments')" :class="{active: activeTab === 'attachments'}" class="nav-link" href="javascript:">Załączniki</a></li>
      <li v-if="showPollTab" class="nav-item"><a @click="switchTab('poll')" :class="{active: activeTab === 'poll'}" class="nav-link" href="javascript:">Ankieta</a></li>
      <li class="nav-item"><a @click="switchTab('preview')" :class="{active: activeTab === 'preview'}" class="nav-link" href="javascript:">Podgląd</a></li>
    </ul>

    <div class="tab-content">
      <div :class="{active: activeTab === 'textarea'}" class="tab-pane">
        <vue-toolbar :input="() => $refs.textarea"></vue-toolbar>

        <vue-prompt :source="`/completion/prompt/${topic.id || ''}`">
          <textarea
            v-autosize
            v-model="post.text"
            v-paste:success="addAttachment"
            :class="{'is-invalid': 'text' in errors}"
            @keydown.ctrl.enter="save"
            @keydown.meta.enter="save"
            @keydown.esc="cancel"
            name="text"
            class="form-control"
            ref="textarea"
            rows="4"
            tabindex="2"
            placeholder="Kliknij, aby dodać treść"
            data-popover='{"placement": "top", "offset": "16%,14px", "message": "Markdown jest obsługiwany. Ctrl+V wkleja obraz ze schowka."}'
          ></textarea>

          <vue-error :message="errors['text']"></vue-error>
        </vue-prompt>
      </div>

      <div :class="{active: activeTab === 'attachments'}" class="tab-pane post-content">
        <div class="card card-default">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>Nazwa pliku</th>
                <th>Typ MIME</th>
                <th>Data dodania</th>
                <th>Rozmiar</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <template v-if="post.attachments.length">
                <tr v-for="attachment in post.attachments" :key="attachment.id">
                  <td>
                    <a @click="insertAtCaret(attachment)" href="javascript:">{{ attachment.name }}</a>
                  </td>
                  <td>{{ attachment.mime }}</td>
                  <td><vue-timeago :datetime="attachment.created_at"></vue-timeago></td>
                  <td>{{ Math.round(attachment.size / 1024) }} kB</td>
                  <td>
                    <button @click="deleteAttachment({ post, attachment })" type="button" title="Usuń załącznik" class="btn btn-secondary btn-sm">
                      <i class="fas fa-times"></i>
                    </button>
                  </td>
                </tr>
              </template>
              <tr v-else>
                <td colspan="5" class="text-center">Brak załączników.</td>
              </tr>
            </tbody>
          </table>
          <div class="card-footer">
            <p class="text-muted"><small>Każdy załącznik może zawierać maksymalnie <strong>{{ uploadMaxSize }}MB</strong>. Dostępne rozszerzenia: <strong>{{ uploadMimes }}</strong></small></p>

            <input @change="upload" type="file" ref="attachment" style="visibility: hidden; height: 1px; width: 0">
            <button @click="openDialog" type="button" class="btn btn-primary btn-sm">Dodaj załącznik</button>
          </div>
        </div>
      </div>

      <div v-if="showPollTab" :class="{active: activeTab === 'poll'}" class="tab-pane post-content">
        <div class="form-group row">
          <label class="col-md-4 col-form-label text-right">Tytuł ankiety</label>

          <div class="col-md-6">
            <vue-text :value.sync="poll.title"></vue-text>
            <vue-error :message="errors['poll.title']"></vue-error>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-4 col-form-label text-right">Odpowiedzi w ankiecie</label>

          <div class="col-md-6">
            <div v-for="(item, index) in poll.items" :key="item.id" class="input-group mb-1">
              <div class="input-group-prepend">
                <a @click="removeItem(item)" class="input-group-text text-decoration-none" href="javascript:">
                  <i :class="{'text-danger': poll.items.length > 2, 'text-muted': poll.items.length <= 2}" title="Usuń odpowiedź" class="fas fa-fw fa-minus-circle"></i>
                </a>
              </div>

              <vue-text
                :value.sync="item.text"
                :is-invalid="`poll.items.${index}.text` in errors"
                class="input-sm"
                @keydown.enter.native="addItem"
                placeholder="Naciśnij Enter, aby dodać kolejną pozycję"
              ></vue-text>

              <vue-error :message="errors[`poll.items.${index}.text`]"></vue-error>
            </div>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-4 col-form-label text-right">Liczba możliwych odpowiedzi</label>

          <div class="col-md-6">
            <vue-text :value.sync="poll.max_items"></vue-text>
            <vue-error :message="errors['poll.max_items']"></vue-error>

            <span class="form-text text-muted">Minimalnie jedna możliwa odpowiedź w ankiecie.</span>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-md-4 col-form-label text-right">Długość działania</label>

          <div class="col-md-6">
            <vue-text :value.sync="poll.length"></vue-text>
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

      <div :class="{active: activeTab === 'preview'}" class="tab-pane post-content">
        <div v-html="post.html"></div>
      </div>
    </div>

    <div v-if="showTagsInput" class="form-group">
      <label class="col-form-label">Tagi <em v-if="requireTag">*</em></label>

      <vue-tags-inline
        :tags="topic.tags"
        :class="{'is-invalid': 'tags' in errors}"
        :placeholder="requireTag ? 'Minimum 1 tag jest wymagany': 'Np. c#, .net'"
        @change="toggleTag"
      ></vue-tags-inline>

      <vue-error :message="errors['tags']"></vue-error>
    </div>

    <div v-if="showStickyCheckbox" class="form-group">
      <div class="custom-control custom-checkbox">
        <input v-model="topic.is_sticky" type="checkbox" class="custom-control-input" id="is-sticky">
        <label class="custom-control-label" for="is-sticky">Przyklejony wątek</label>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" tabindex="4" title="Kliknij, aby zapisać (Ctrl+Enter)" class="btn btn-primary btn-sm" @click.native.prevent="save">
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
  import { Ref, Prop, Emit, Watch } from "vue-property-decorator";
  import store from "../../store";
  import VueAutosize from '../../plugins/autosize';
  import VueAutosave from '../../plugins/autosave';
  import VuePrompt from '../forms/prompt.vue';
  import VueButton from '../forms/button.vue';
  import VueTagsInline from '../forms/tags-inline.vue';
  import VuePaste from '../../plugins/paste.js';
  import VueToolbar from '../../components/forms/toolbar.vue';
  import VueTimeago from '../../plugins/timeago';
  import { Post, PostAttachment, Topic, Tag } from "../../types/models";
  import { mapMutations, mapState, mapGetters } from "vuex";
  import axios from 'axios';
  import Textarea from "../../libs/textarea";
  import VueError from '../forms/error.vue';
  import VueText from '../forms/text.vue';
  import Prism from 'prismjs';

  Vue.use(VueAutosize);
  Vue.use(VueAutosave);
  Vue.use(VuePaste, {url: '/Forum/Paste'});
  Vue.use(VueTimeago);

  @Component({
    name: 'forum-form',
    store,
    components: {
      'vue-button': VueButton,
      'vue-prompt': VuePrompt,
      'vue-toolbar': VueToolbar,
      'vue-tags-inline': VueTagsInline,
      'vue-error': VueError,
      'vue-text': VueText
    },
    computed: {
      ...mapGetters('topics', ['topic']),
      ...mapState('poll', ['poll'])
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
      ...mapMutations('posts', ['deleteAttachment'])
    }
  })
  export default class VueForm extends Vue {
    isProcessing = false;
    activeTab = 'textarea';
    errors = {};

    @Ref()
    readonly textarea!: HTMLTextAreaElement;

    @Ref('attachment')
    readonly attachment!: HTMLInputElement;

    @Prop({default: false})
    readonly showTitleInput!: boolean;

    @Prop({default: false})
    readonly showTagsInput!: boolean;

    @Prop({default: false})
    readonly showStickyCheckbox!: boolean;

    @Prop({default: 20})
    readonly uploadMaxSize!: number;

    @Prop()
    readonly uploadMimes!: string;

    @Prop({default: false})
    readonly requireTag!: boolean;

    @Prop({default() {
      return {
        text: '',
        html: '',
        attachments: []
      }
    }})
    post!: Post;

    topic!: Topic;

    @Emit()
    cancel() { }

    @Watch('activeTab')
    showPreview(newValue) {
      if (newValue !== 'preview') {
        return;
      }

      axios.post('/Forum/Preview', {text: this.post.text}).then(result => {
        this.post.html = result.data;

        this.$nextTick(() => Prism.highlightAll());
      });
    }

    created() {
      if (this.exists) {
        return;
      }

      this.post.text = this.$loadDraft(this.draftKey) as string;
      this.$watch('post.text', newValue => this.$saveDraft(this.draftKey, newValue));
    }

    save() {
      this.isProcessing = true;
      this.errors = {};

      store.commit('topics/init', [ this.topic ]);

      store.dispatch('posts/save', this.post)
        .then(result => {
          this.$emit('save', result.data);

          // post was recently created. we're not editing it
          if ('id' in this.topic && !this.exists) {
            this.post.text = '';
            this.post.html = '';
            this.post.attachments = [];

            window.location.hash = `id${result.data.id}`;
          }

          this.$nextTick(() => {
            // remove local storage data after clearing input
            this.$removeDraft(this.draftKey);

            Prism.highlightAll();
          });
        })
        .catch(err => {
          if (err.response.status !== 422) {
            return;
          }

          this.errors = err.response.data.errors;
        })
        .finally(() => this.isProcessing = false);
    }

    switchTab(activeTab: string) {
      this.activeTab = activeTab;
    }

    openDialog() {
      (this.$refs.attachment as HTMLInputElement).click();
    }

    upload() {
      let form = new FormData();
      form.append('attachment', (this.$refs.attachment as HTMLInputElement).files![0]);

      this.isProcessing = true;

      store.dispatch('posts/upload', { post: this.post, form })
        .finally(() => this.isProcessing = false);
    }

    addAttachment(attachment: PostAttachment) {
      store.commit('posts/addAttachment', { post: this.post, attachment });
      this.insertAtCaret(attachment);
    }

    insertAtCaret(attachment: PostAttachment) {
      const textarea = new Textarea(this.$refs.textarea);
      const suffix = attachment.name.split('.').pop()!.toLowerCase();

      textarea.insertAtCaret('', '', (['png', 'jpg', 'jpeg', 'gif'].includes(suffix) ? '!' : '') + '[' + attachment.name + '](' + attachment.url + ')');
      this.post.text = textarea.textarea.value;

      this.switchTab('textarea');
    }

    toggleTag(tag: Tag) {
      store.commit('topics/toggleTag', { topic: this.topic, tag });
    }

    get showPollTab() {
      return !this.topic || this.topic.first_post_id === this.post.id;
    }

    get exists() {
      return this.post.id !== undefined;
    }

    private get draftKey(): string {
      return `topic-${this.topic.id ? this.topic.id : ''}`
    }
  }
</script>

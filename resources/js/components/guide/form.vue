<template>
  <form>
    <vue-form-group :errors="errors['title']" label="Tytuł *">
      <vue-text
        v-model="guide.title"
        :is-invalid="'title' in errors"
        name="title"
      />
    </vue-form-group>

    <vue-form-group :errors="errors['role']" label="Rola *">
      <vue-select
        v-model="guide.role"
        :options="roles"
        placeholder="--"
        :is-invalid="'role' in errors"
        name="role"
      />
    </vue-form-group>

    <vue-form-group :errors="errors['excerpt']" label="Opis">
      <vue-markdown
        @save="save"
        v-model="guide.excerpt"
        :is-invalid="'excerpt' in errors"
        name="excerpt"
      />
    </vue-form-group>

    <vue-form-group :errors="errors['text']" label="Odpowiedź *">
      <vue-markdown
        @save="save"
        v-model="guide.text"
        :is-invalid="'text' in errors"
        :assets.sync="guide.assets"
      >
        <template v-slot:bottom>
          <div class="row no-gutters p-1">
            <vue-tags-inline
              :tags="guide.tags"
              :class="{'is-invalid': 'tags' in errors}"
              @change="TOGGLE_TAG"
              placeholder="...inny? kliknij, aby wybrać tag"
            />

            <vue-error :message="errors['tags']"></vue-error>
          </div>
        </template>
      </vue-markdown>
    </vue-form-group>

    <div class="row mt-2">
      <div class="col-12">
        <vue-button :disabled="isProcessing" tabindex="4" title="Kliknij, aby zapisać (Ctrl+Enter)" class="btn btn-primary btn-sm" @click.native.prevent="save">
          Zapisz
        </vue-button>

        <button v-if="guide.id" @click="cancel" title="Anuluj (Esc)" class="btn btn-sm btn-danger mr-2" tabindex="3">
          Anuluj
        </button>
      </div>
    </div>
  </form>
</template>

<script lang="ts">
import VueButton from '@/components/forms/button.vue';
import VueError from '@/components/forms/error.vue';
import VueFormGroup from "@/components/forms/form-group.vue";
import VueMarkdown from '@/components/forms/markdown.vue';
import VueSelect from '@/components/forms/select.vue';
import VueTagsInline from '@/components/forms/tags-inline.vue';
import VueText from '@/components/forms/text.vue';
import {GuideMixin} from "@/components/mixins/guide";
import store from '@/store';
import Vue from 'vue';
import {mapMutations, mapState} from 'vuex';
import {default as mixins} from '../mixins/user.js';

export default Vue.extend({
  name: 'VueForm',
  mixins: [mixins, GuideMixin],
  store,
  components: {
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-markdown': VueMarkdown,
    'vue-button': VueButton,
    'vue-tags-inline': VueTagsInline,
    'vue-error': VueError,
  },
  props: {
    popularTags: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      errors: {},
      isProcessing: false,
    };
  },
  computed: {
    ...mapState('guides', ['guide']),
    providedPopularTags() {
      return this.popularTags;
    },
  },
  methods: {
    ...mapMutations('guides', ['TOGGLE_TAG']),
    cancel() {
      store.commit('guides/EDIT');
    },
    save() {
      this.isProcessing = true;

      return store.dispatch('guides/save')
        .then(response => {
          this.$emit('save');

          if (response.status === 201) {
            window.location.href = response.data.url;
          }
        })
        .catch(err => {
          if (err.response?.status !== 422) {
            return;
          }

          this.errors = err.response?.data.errors;
        })
        .finally(() => this.isProcessing = false);
    },
  },
  provide() {
    return {
      popularTags: this.providedPopularTags,
    };
  },
});
</script>

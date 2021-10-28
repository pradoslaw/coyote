<template>
  <form>
    <vue-form-group :errors="errors['title']" label="Tytuł">
      <vue-text
        v-model="guide.title"
        :is-invalid="'title' in errors"
        name="title"
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

    <vue-form-group :errors="errors['text']" label="Odpowiedź">
      <vue-markdown
        @save="save"
        v-model="guide.text"
        :is-invalid="'text' in errors"
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
  import Vue from 'vue';
  import Component from "vue-class-component";
  import {Prop, ProvideReactive} from "vue-property-decorator";
  import { Guide, Tag } from '@/types/models';
  import { default as mixins } from '../mixins/user';
  import VueButton from '@/components/forms/button.vue';
  import VueTagsInline from '@/components/forms/tags-inline.vue';
  import VueMarkdown from '@/components/forms/markdown.vue';
  import VueText from '@/components/forms/text.vue';
  import VueError from '@/components/forms/error.vue';
  import { mapActions, mapMutations, mapState } from 'vuex';
  import VueFormGroup from "@/components/forms/form-group.vue";
  import store from '@/store';

  @Component({
    mixins: [ mixins ],
    store,
    components: {
      'vue-form-group': VueFormGroup,
      'vue-text': VueText,
      'vue-markdown': VueMarkdown,
      'vue-button': VueButton,
      'vue-tags-inline': VueTagsInline,
      'vue-error': VueError
    },
    computed: {
      ...mapState('guides', ['guide'])
    },
    methods: {
      ...mapMutations('guides', ['TOGGLE_TAG'])
    },
    inject: []
  })
  export default class VueForm extends Vue {
    errors = {};
    private isProcessing = false;

    @Prop({default: () => []})
    @ProvideReactive('popularTags')
    readonly popularTags!: Tag[];

    cancel() {
      store.commit('guides/EDIT');
    }

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
    }
  }
</script>


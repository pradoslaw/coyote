<template>
  <form>
    <vue-form-group :errors="errors['title']" label="Tytuł">
      <vue-text name="title" v-model="guide.title" :is-invalid="'title' in errors"></vue-text>

    </vue-form-group>

    <vue-form-group :errors="errors['excerpt']" label="Opis">
      <vue-markdown name="excerpt" v-model="guide.excerpt" :is-invalid="'excerpt' in errors"></vue-markdown>
    </vue-form-group>

    <vue-form-group :errors="errors['text']" label="Odpowiedz">
      <vue-markdown v-model="guide.text" :is-invalid="'text' in errors"></vue-markdown>
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
  import { Prop } from "vue-property-decorator";
  import { Guide } from '@/types/models';
  import { default as mixins } from '../mixins/user';
  import VueButton from '@/components/forms/button.vue';
  import VueTagsInline from '@/components/forms/tags-inline.vue';
  import VueMarkdown from '@/components/forms/markdown.vue';
  import VueText from '@/components/forms/text.vue';
  import {mapActions, mapState} from 'vuex';
  import VueFormGroup from "@/components/forms/form-group.vue";

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-form-group': VueFormGroup,
      'vue-text': VueText,
      'vue-markdown': VueMarkdown,
      'vue-button': VueButton
    },
    computed: {
      ...mapState('guides', ['guide'])
    },
    methods: {
      ...mapActions('guides', ['save'])
    }
  })
  export default class VueForm extends Vue {
    private errors = {};
    private isProcessing = false;

    cancel() {
      this.$store.commit('guides/edit');
    }
  }
</script>


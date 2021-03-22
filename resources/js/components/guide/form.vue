<template>
  <form>
    <vue-form-group :errors="errors['title']" label="Tytuł">
      <vue-text name="title" v-model="guide.title" :is-invalid="'title' in errors"></vue-text>

    </vue-form-group>
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
  import { mapState } from 'vuex';
  import VueFormGroup from "@/components/forms/form-group.vue";

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-form-group': VueFormGroup,
      'vue-text': VueText
    },
    computed: {
      ...mapState('guides', ['guide'])
    }
  })
  export default class VueForm extends Vue {
    private errors = {};
  }
</script>


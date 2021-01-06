<template>
  <ul v-if="tags.length" class="tag-clouds">
    <li v-for="tag in tags">
      <a :href="tag.url">
        <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

        {{ tag.real_name || tag.name }}
      </a>
    </li>
  </ul>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop } from "vue-property-decorator";
  import { Tag } from '@/types/models';

  @Component
  export default class VueTags extends Vue {
    @Prop({default: () => []})
    readonly tags!: Tag[];
  }
</script>

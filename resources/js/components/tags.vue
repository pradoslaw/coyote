<template>
  <ul v-if="tags.length" class="tag-clouds">
    <li v-for="tag in tags">
      <component :is="tagName" :href="tag.url">
        <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

        {{ tag.real_name || tag.name }}

        <a v-if="editable" @click="deleteTag(tag)" class="remove"><i class="fa fa-times"></i></a>
      </component>

      <vue-progress-bar
        v-if="editable || tag.priority"
        :editable="editable"
        v-model="tag.priority"
        @click="setPriority(tag, ...arguments)"
      ></vue-progress-bar>
    </li>
  </ul>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop, Emit } from "vue-property-decorator";
  import { Tag } from '@/types/models';
  import VueProgressBar from "@/components/progress-bar.vue";

  @Component({
    components: { 'vue-progress-bar': VueProgressBar }
  })
  export default class VueTags extends Vue {
    @Prop({default: () => []})
    readonly tags!: Tag[];

    @Prop({default: false})
    readonly editable!: boolean;

    @Emit('change')
    setPriority(tag: Tag, priority: number) {
      tag.priority = priority;

      return tag;
    }

    @Emit('delete')
    deleteTag(tag: Tag) {
      return tag;
    }

    get tagName() {
      return this.editable ? 'span' : 'a';
    }
  }
</script>

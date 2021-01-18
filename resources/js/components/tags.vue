<template>
  <ul v-if="tags.length" class="tag-clouds">
    <li v-for="tag in tags">
      <component :is="tagName" :href="tag.url">
        <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

        {{ tag.real_name || tag.name }}

        <a v-if="editable" @click="deleteTag(tag)" class="remove"><i class="fa fa-times"></i></a>
      </component>

      <div v-if="editable" class="d-inline" @mouseleave="hover = null">
        <span
          v-for="i in [1, 2, 3]"
          :aria-label="tooltips[i - 1]"
          @mouseover="hover = i"
          @click="setPriority(tag, i)"
          data-balloon-pos="down"
        >
          <i class="fas fa-circle" :class="{'text-primary': tag.priority >= i, 'text-muted': tag.priority < i}"></i>
        </span>
      </div>

      <div v-else-if="tag.priority" class="d-inline">
        <span
          v-for="i in [1, 2, 3]"
          :aria-label="tooltips[i - 1]"
          data-balloon-pos="down"
        >
          <i class="fas fa-circle" :class="{'text-primary': tag.priority >= i, 'text-muted': tag.priority < i}"></i>
        </span>
      </div>
    </li>
  </ul>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop, Emit } from "vue-property-decorator";
  import { Tag } from '@/types/models';

  @Component
  export default class VueTags extends Vue {
    private hover: number | null = null;

    @Prop({default: () => []})
    readonly tags!: Tag[];

    @Prop({default: false})
    readonly editable!: boolean;

    @Prop({default: () => ['podstawy', 'średnio zaawansowany', 'zaawansowany']})
    readonly tooltips!: string[];

    @Emit('priority')
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

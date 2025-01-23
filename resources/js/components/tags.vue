<template>
  <ul v-if="tags.length" class="tag-clouds">
    <li v-for="tag in tags">
      <component :is="tagName" :href="tag.url" class="neon-tag">
        <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">
        {{ tag.real_name || tag.name }}
        <a v-if="editable" @click="deleteTag(tag)" class="remove">
          <vue-icon name="tagRemove"/>
        </a>
      </component>
      <vue-progress-bar
        v-if="editable || tag.priority"
        :editable="editable"
        v-model="tag.priority"
        @click="value => setPriority(tag, value)"
      />
    </li>
  </ul>
</template>

<script lang="ts">
import {Tag} from "../types/models";
import VueIcon from "./icon";
import VueProgressBar from "./progress-bar.vue";

export default {
  name: 'VueTags',
  components: {
    'vue-progress-bar': VueProgressBar,
    'vue-icon': VueIcon,
  },
  props: {
    tags: {
      type: Array,
      default: () => [],
    },
    editable: {
      type: Boolean,
      default: false,
    },
  },
  methods: {
    setPriority(tag: Tag, priority: number) {
      tag.priority = priority;
      this.$emit('change', tag);
    },
    deleteTag(tag: Tag) {
      this.$emit('delete', tag);
    },
  },
  computed: {
    tagName() {
      return this.editable ? 'span' : 'a';
    },
  },
};
</script>

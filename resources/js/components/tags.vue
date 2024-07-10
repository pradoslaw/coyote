<template>
  <ul v-if="tags.length" class="tag-clouds">
    <li v-for="tag in tags">
      <component :is="tagName" :href="tag.url">
        <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

        {{ tag.real_name || tag.name }}

        <a v-if="editable" @click="deleteTag(tag)" class="remove">
          <i class="fa fa-xmark"></i>
        </a>
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
import {Tag} from "../types/models";
import VueProgressBar from "./progress-bar.vue";

export default Vue.extend({
  name: 'VueTags',
  components: {'vue-progress-bar': VueProgressBar},
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
});
</script>

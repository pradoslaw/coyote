<template>
  <component
    :is="tagName"
    v-profile="user.id"
    :class="[{'badge badge-primary': owner}, 'username']"
    :style="{textDecoration: this.user.is_blocked ? 'line-through' : ''}"
  >{{ user.name }}
  </component>
</template>

<script lang="ts">
import Vue from 'vue';
import {default as mixins} from './mixins/user.js';

export default Vue.extend({
  name: 'user-name',
  mixins: [mixins],
  props: {
    user: {
      type: Object,
      required: true,
    },
    owner: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    tagName() {
      return this.user.deleted_at ? 'del' : 'a';
    },
  },
});
</script>

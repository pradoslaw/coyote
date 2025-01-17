<template>
  <component
    :is="tagName"
    v-profile="user.id"
    class="username"
    :class="owner ? 'neon-username-author': 'neon-color-link'"
    :style="{textDecoration: this.user.is_blocked ? 'line-through' : ''}"
    v-text="user.name"/>
</template>

<script lang="ts">
import {default as mixins} from './mixins/user.js';

export default {
  name: 'user-name',
  mixins: [mixins],
  props: {
    user: {type: Object, required: true},
    owner: {type: Boolean, default: false},
  },
  computed: {
    tagName(): string {
      return this.user.deleted_at ? 'del' : 'a';
    },
  },
};
</script>

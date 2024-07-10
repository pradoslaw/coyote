<template>
  <a :href="item.url" class="text-truncate" tabindex="-1">
    <span v-html="highlight(item.title, value)"></span>

    <small class="forum-name text-muted">w {{ item.forum.name }}</small>
  </a>
</template>

<script lang="ts">
import Vue from 'vue';
import DecoratorMixin from '../mixin';

export default Vue.extend({
  mixins: [DecoratorMixin],
});
</script>

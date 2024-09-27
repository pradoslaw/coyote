<template>
  <a :href="item.url" class="text-truncate" tabindex="-1">
    <span v-html="highlight(item.title)"/>
    {{ ' ' }}
    <small class="forum-name text-muted">
      w {{ item.forum.name }}
    </small>
  </a>
</template>

<script lang="ts">
import DecoratorMixin from '../mixin';

export default {
  mixins: [DecoratorMixin],
};
</script>

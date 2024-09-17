<template>
  <a :href="item.url" class="text-truncate" tabindex="-1">
    <span v-html="highlight(text)"/>
  </a>
</template>

<script lang="ts">
import DecoratorMixin from '../mixin';

export default {
  mixins: [DecoratorMixin],
  props: {
    item: {
      type: Object,
      required: true,
    },
  },
};
</script>

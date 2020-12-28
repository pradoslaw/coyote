<template>
  <a :href="item.url" class="text-truncate" tabindex="-1">
    <span v-html="highlight(item.title, value)"></span>

    <small class="forum-name text-muted">w {{ item.forum.name }}</small>
  </a>
</template>

<script lang="ts">
  import { Component, Mixins } from "vue-property-decorator";
  import DecoratorMixin from '../mixin';

  @Component
  export default class TopicDecorator extends Mixins(DecoratorMixin) {
    //
  }
</script>

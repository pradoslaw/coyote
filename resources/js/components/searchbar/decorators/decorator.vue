<template>
  <a :href="item.url" class="text-truncate" tabindex="-1"><span v-html="highlight(text)"></span></a>
</template>

<script lang="ts">
  import { Mixins } from "vue-property-decorator";
  import DecoratorMixin from '../mixin';

  export default abstract class Decorator extends Mixins(DecoratorMixin) {
     abstract text: string;
  }
</script>

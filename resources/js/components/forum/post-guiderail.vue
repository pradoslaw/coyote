<template>
  <div class="position-absolute post-guiderail post-guiderail-to-parent"/>
  <div class="position-absolute post-guiderail post-guiderail-to-sibling" v-if="hasNextSibling"/>
</template>

<script>
export default {
  name: 'VuePostGuiderail',
  props: {
    hasNextSibling: {required: false, default: false},
  },
};
</script>

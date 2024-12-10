<template>
  <div class="position-absolute post-guiderail post-guiderail-to-parent"/>
  <div class="position-absolute post-guiderail post-guiderail-to-sibling" v-if="hasNextSibling"/>
  <div class="position-absolute post-guiderail post-guiderail-of-parent" v-if="parentHasNextSibling"/>
</template>

<script>
export default {
  name: 'VuePostGuiderail',
  props: {
    hasNextSibling: {required: false, default: false},
    parentHasNextSibling: {required: false, default: false},
  },
};
</script>

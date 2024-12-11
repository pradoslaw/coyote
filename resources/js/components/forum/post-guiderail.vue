<template>
  <div class="position-absolute post-guiderail post-guiderail-of-parent" v-if="parentHasNextSibling"/>
  <div class="position-absolute post-guiderail post-guiderail-to-sibling" v-if="hasNextSibling"/>
  <div class="position-absolute post-guiderail post-guiderail-to-parent">
    <div class="position-absolute post-guiderail-button d-flex justify-content-center align-items-center cursor-pointer" @click="toggle" style="font-size:0.7em;">
      <vue-icon name="postGuiderailExpanded" v-if="expanded"/>
      <vue-icon name="postGuiderailCollapsed" v-else/>
    </div>
  </div>
</template>

<script lang="ts">
import VueIcon from '../icon';

export default {
  name: 'VuePostGuiderail',
  components: {VueIcon},
  emits: ['toggle'],
  props: {
    expanded: {required: true, type: Boolean},
    hasNextSibling: {required: false, default: false},
    parentHasNextSibling: {required: false, default: false},
  },
  methods: {
    toggle(): void {
      this.$emit('toggle', !this.$props.expanded);
    },
  },
};
</script>

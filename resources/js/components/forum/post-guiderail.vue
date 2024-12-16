<template>
  <div
    v-for="cssClass in parentGuiderails"
    class="position-absolute post-guiderail post-guiderail-of-parent" :class="cssClass"
  />
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
    parentLevels: {required: false, type: Array},
  },
  methods: {
    toggle(): void {
      this.$emit('toggle', !this.$props.expanded);
    },
  },
  computed: {
    parentGuiderails(): number[] {
      return this.$props.parentLevels.map(parentLevel => {
        if (parentLevel === 1) {
          return 'post-guiderail-of-parent-1';
        }
        return 'post-guiderail-of-parent-2';
      });
    },
  },
};
</script>

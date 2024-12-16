<template>
  <div
    v-for="cssClass in parentGuiderails"
    class="position-absolute post-guiderail" :class="cssClass"
  />
  <div class="position-absolute post-guiderail post-guiderail-to-parent" v-if="linksToParent">
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
    parentLevels: {required: false, type: Array},
    linksToParent: {required: false, type: Boolean},
  },
  methods: {
    toggle(): void {
      this.$emit('toggle', !this.$props.expanded);
    },
  },
  computed: {
    parentGuiderails(): number[] {
      return this.$props.parentLevels.map(parentLevel => {
        const parentLevels = {
          0: 'post-guiderail-to-sibling',
          1: 'post-guiderail-of-parent post-guiderail-of-parent-1',
          2: 'post-guiderail-of-parent post-guiderail-of-parent-2',
          3: 'post-guiderail-of-parent post-guiderail-of-parent-3',
          4: 'post-guiderail-of-parent post-guiderail-of-parent-4',
          5: 'post-guiderail-of-parent post-guiderail-of-parent-5',
          6: 'post-guiderail-of-parent post-guiderail-of-parent-6',
          7: 'post-guiderail-of-parent post-guiderail-of-parent-7',
          8: 'post-guiderail-of-parent post-guiderail-of-parent-8',
          9: 'post-guiderail-of-parent post-guiderail-of-parent-9',
        };
        return parentLevels[parentLevel];
      });
    },
  },
};
</script>

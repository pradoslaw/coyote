<template>
  <div
    v-for="cssClass in parentGuiderails"
    class="position-absolute post-guiderail" :class="cssClass"
  />
  <div class="position-absolute post-guiderail post-guiderail-to-parent" v-if="linksToParent"/>
  <div class="position-absolute post-guiderail post-guiderail-to-child" v-if="linksToChild"/>
  <div class="position-absolute post-guiderail post-guiderail-to-toggle" v-if="toggleVisible">
    <div class="position-absolute post-guiderail-button d-flex justify-content-center align-items-center cursor-pointer" @click="toggle" style="font-size:0.7em;">
      <vue-icon name="postGuiderailExpanded" v-if="expanded"/>
      <vue-icon name="postGuiderailCollapsed" v-else/>
    </div>
  </div>
</template>

<script lang="ts">
import VueIcon from '../icon';

export type ChildLink = 'none' | 'toggle-only' | 'toggle-and-link';

export default {
  name: 'VuePostGuiderail',
  components: {VueIcon},
  emits: ['toggle'],
  props: {
    expanded: {required: true, type: Boolean},
    parentLevels: {type: Array},
    linksToParent: {type: Boolean},
    linkToChild: {type: String},
  },
  methods: {
    toggle(): void {
      this.$emit('toggle', !this.$props.expanded);
    },
  },
  computed: {
    toggleVisible(): boolean {
      return this.$props.linkToChild !== 'none';
    },
    linksToChild(): boolean {
      return this.$props.linkToChild === 'toggle-and-link';
    },
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
          10: 'post-guiderail-of-parent post-guiderail-of-parent-10',
        };
        return parentLevels[parentLevel];
      });
    },
  },
};
</script>

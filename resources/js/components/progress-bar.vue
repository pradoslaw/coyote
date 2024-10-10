<template>
  <div class="progress-bar-dotted">
    <span
      v-for="i in max"
      :aria-label="tooltips[i - 1]"
      :class="[{editable}, valueLocal < i ? 'text-muted' : 'text-primary']"
      @click="setValue(i)"
      data-balloon-pos="down">
      <vue-icon name="tagRank"/>
    </span>
  </div>
</template>

<script lang="ts">
import VueIcon from "./icon";
import {default as mixin} from './mixins/form.js';

export default {
  name: 'VueProgressBar',
  emits: ['click'],
  mixins: [mixin],
  components: {'vue-icon': VueIcon},
  props: {
    modelValue: {
      type: Number,
      required: true,
    },
    editable: {
      type: Boolean,
      default: false,
    },
    max: {
      type: Number,
      default: 3,
    },
    tooltips: {
      type: Array,
      default: () => ['podstawy', 'średnio zaawansowany', 'zaawansowany'],
    },
  },
  methods: {
    setValue(value) {
      if (!this.editable) {
        return;
      }
      this.$emit('click', value);
    },
  },
};
</script>

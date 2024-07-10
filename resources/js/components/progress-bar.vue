<template>
  <div class="progress-bar-dotted">
    <span
      v-for="i in max"
      :aria-label="tooltips[i - 1]"
      :class="{'editable': editable}"
      @click="setValue(i)"
      data-balloon-pos="down"
    >
      <i class="fas fa-circle" :class="{'text-primary': valueLocal >= i, 'text-muted': valueLocal < i}"></i>
    </span>
  </div>
</template>

<script lang="ts">
import {default as mixin} from '@/components/mixins/form.js';
import Vue from 'vue';

export default Vue.extend({
  name: 'VueProgressBar',
  mixins: [mixin],
  props: {
    value: {
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
});
</script>

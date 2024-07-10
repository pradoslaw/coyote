<template>
  <div :class="{'show': isDropdownVisible}" v-on-clickaway="hideDropdown" class="dropdown-menu">
    <slot></slot>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import { mixin as clickaway } from 'vue-clickaway';

export default Vue.extend({
  name: 'VueDropdownMenu',
  mixins: [clickaway],
  data() {
    return {
      isDropdownVisible: false,
    };
  },
  methods: {
    hideDropdown(event) {
      if (event && !event.target.classList.contains('dropdown-toggle')) {
        this.isDropdownVisible = false;
      }
    },
    toggle() {
      this.isDropdownVisible = !this.isDropdownVisible;
    },
  },
});
</script>


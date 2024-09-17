<template>
  <div :class="{'show': isDropdownVisible}" v-click-away="hideDropdown" class="dropdown-menu">
    <slot></slot>
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import clickAway from "../clickAway.js";

export default Vue.extend({
  name: 'VueDropdownMenu',
  directives: {clickAway},
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

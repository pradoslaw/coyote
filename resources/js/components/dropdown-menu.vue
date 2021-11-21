<template>
  <div :class="{'show': isDropdownVisible}" v-on-clickaway="hideDropdown" class="dropdown-menu">
    <slot></slot>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { mixin as clickaway } from 'vue-clickaway';

  // @ts-ignore
  @Component({
    mixins: [ clickaway ]
  })
  export default class VueDropdownMenu extends Vue {
    public isDropdownVisible = false;

    hideDropdown(event) {
      if (event && !event.target.classList.contains('dropdown-toggle')) {
        this.isDropdownVisible = false;
      }
    }

    toggle() {
      this.isDropdownVisible = !this.isDropdownVisible;
    }
  }
</script>


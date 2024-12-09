<template>
  <div :class="['nav', `nav-${this.type}`, 'neon-tabber']">
    <div v-for="(item, key) in items" :key="key" class="nav-item">
      <a
        :href="typeof key === 'string' ? key : 'javascript:'"
        class="nav-link neon-tabber-tab"
        :class="{'active neon-tabber-tab-active': key === currentTab}"
        @click="$emit('change', key)"
        v-text="item"
      />
    </div>
    <slot/>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    items: {
      type: [Array, Object],
      default: [],
    },
    currentTab: null,
    type: {
      type: String,
      default: 'tabs',
      validator(value: string): boolean {
        return value === 'tabs' || value === 'pills';
      },
    },
  },
};
</script>

<template>
  <div :class="`nav nav-${this.type}`">
    <div v-for="(item, key) in items" :key="key" class="nav-item">
      <a @click="$emit('click', key)" :class="{'active': key === currentTab}" :href="typeof key === 'string' ? key : 'javascript:'" class="nav-link">{{ item }}</a>
    </div>

    <slot></slot>
  </div>
</template>

<script>
  export default {
    props: {
      items: {
        type: [Array, Object],
        default: []
      },
      currentTab: null,
      type: {
        type: String,
        default: 'tabs',
        validator(value) {
          return value === 'tabs' || value === 'pills';
        }
      }
    }
  }
</script>

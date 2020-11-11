<template>
  <ul class="nav nav-tabs">
    <li v-for="(item, key) in items" :key="key" class="nav-item">
      <a @click="$emit('click', key)" :class="{'active': key === currentTab}" class="nav-link" href="javascript:">{{ item }}</a>
    </li>
  </ul>
</template>

<script>
  export default {
    props: {
      items: {
        type: [Array, Object],
        default: []
      },
      currentTab: null
    }
  }
</script>

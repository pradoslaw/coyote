<template>
  <div class="position-relative">
    <img :src="url" :alt="name" class="d-block mw-100">

    <div v-if="isOnline" class="is-online"></div>
  </div>
</template>

<script lang="js">
  export default {
    props: {
      name: {
        type: String
      },
      id: {
        type: Number
      },
      photo: {
        type: String,
        default: '/img/avatar.png'
      },
      isOnline: {
        type: Boolean
      }
    },
    computed: {
      url() {
        return this.photo ||  '/img/avatar.png';
      }
    }
  }
</script>


<template>
  <div class="position-relative">
    <object :data="photo || '//'" type="image/png" class="d-block mw-100">
      <img src="/img/avatar.png" :alt="name" class="mw-100">

      <slot></slot>
    </object>

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
        type: String
      },
      isOnline: {
        type: Boolean
      }
    }
  }
</script>


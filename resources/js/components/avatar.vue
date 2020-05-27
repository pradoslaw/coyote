<template>
  <object :data="photo || '//'" type="image/png">
    <img src="/img/avatar.png" :alt="name" class="mw-100">

    <slot></slot>
  </object>
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
      photo: String
    }
  }
</script>

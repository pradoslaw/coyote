<template>
  <a v-profile="id" class="mr-2 d-none d-sm-block position-relative" style="height: 35px">
    <object :data="photo || '//'" type="image/png" class="d-inline-block img-thumbnail" :style="{width: width + 'px', height: width + 'px'}">
      <img src="/img/avatar.png" :alt="name" class="mw-100">

      <slot></slot>
    </object>
  </a>
</template>

<script>
  import { default as mixins } from './mixins/user';

  export default {
    mixins: [ mixins ],
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
      width: {
        type: Number,
        default: 35
      }
    }
  }
</script>

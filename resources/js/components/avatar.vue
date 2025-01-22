<template>
  <div class="position-relative">
    <img :src="photo"
         :alt="name"
         class="d-block user-avatar"
         loading="lazy"
         v-if="hasAvatar"
         :style="{width:'100%','height':'100%','object-fit':'cover','object-position':'center'}">
    <div class="user-avatar default-avatar" v-else>
      <svg viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
        <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="currentColor">
          {{ initials }}
        </text>
      </svg>
    </div>
    <div v-if="isOnline" class="is-online"/>
  </div>
</template>

<script lang="ts">
export default {
  props: {
    name: {type: String},
    photo: {type: String},
    initials: {type: String},
    isOnline: {type: Boolean},
  },
  computed: {
    hasAvatar(): boolean {
      return !!this.photo;
    },
  },
};
</script>

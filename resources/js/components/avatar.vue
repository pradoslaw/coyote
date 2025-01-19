<template>
  <div class="position-relative">
    <div class="user-avatar" style="height:inherit;width:inherit;">
      <img :src="photo" :alt="name" class="d-block mw-100 mh-100" loading="lazy" v-if="hasAvatar">
      <div class="default-avatar" v-else>
        <svg viewBox="0 0 72 72" xmlns="http://www.w3.org/2000/svg">
          <text x="50%" y="50%" dominant-baseline="central" text-anchor="middle" fill="currentColor">
            {{ initials }}
          </text>
        </svg>
      </div>
    </div>
    <div v-if="isOnline" class="is-online neon-is-online"/>
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

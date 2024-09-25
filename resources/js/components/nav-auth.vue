<template>
  <ul class="nav-auth navbar-nav order-2">
    <vue-notifications/>
    <vue-pm/>
  </ul>
</template>

<script lang="ts">
import VueNotification from "./notifications/notifications.vue";
import VuePm from "./pm/inbox.vue";

export default {
  components: {
    'vue-notifications': VueNotification,
    'vue-pm': VuePm,
  },
};
</script>

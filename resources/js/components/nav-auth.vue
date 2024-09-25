<template>
  <vue-notifications/>
  <vue-pm/>
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

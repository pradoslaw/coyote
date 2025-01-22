<template>
  <div :title="message.excerpt" :class="{'unread': ! isRead}" class="notification">
    <div class="media">
      <a :href="`/Profile/${message.user.id}`" title="Kliknij, aby wyświetlić profil użytkownika" class="me-2">
        <div class="neon-avatar-border">
          <vue-avatar
            :photo="message.user.photo"
            :initials="message.user.initials"
            class="i-35"/>
        </div>
      </a>
      <a :href="message.url" class="media-body">
        <header class="text-truncate notification-header">
          <h4>{{ message.user.name }}</h4>
          <small>
            <vue-timeago :datetime="message.created_at"/>
          </small>
        </header>
        <p class="text-truncate notification-content">
          <template v-if="message.folder === SENTBOX">
            <vue-icon name="privateMessageReadAt" v-if="message.read_at"/>
            <span v-else>Ty: </span>
          </template>
          {{ message.excerpt }}
        </p>
      </a>
    </div>

    <a v-if="!isRead" @click="mark" href="javascript:" class="btn-action" title="Oznacz jako przeczytane">
      <vue-icon name="privateMessageMarkAsRead"/>
    </a>
  </div>
</template>

<script>
import {VueTimeAgo} from '../../plugins/timeago';
import store from "../../store/index";
import VueAvatar from '../avatar.vue';
import VueIcon from "../icon";

export default {
  components: {
    'vue-avatar': VueAvatar,
    'vue-timeago': VueTimeAgo,
    'vue-icon': VueIcon,
  },
  props: {
    message: {
      type: Object,
    },
  },
  data() {
    return {
      SENTBOX: 2,
    };
  },
  methods: {
    mark() {
      store.dispatch('messages/mark', this.message);
    },
  },
  computed: {
    isRead() {
      return this.message.folder !== this.SENTBOX ? (this.message.read_at !== null) : true;
    },
  },
};
</script>

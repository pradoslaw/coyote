<template>
  <div :title="message.excerpt" :class="{'unread': ! isRead}" class="notification">
    <div class="media">
      <a :href="`/Profile/${message.user.id}`" title="Kliknij, aby wyświetlić profil użytkownika">
        <vue-avatar :photo="message.user.photo" class="i-35 me-2"></vue-avatar>
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
            <i v-if="message.read_at" class="fas fa-check"></i>
            <span v-else>Ty: </span>
          </template>

          {{ message.excerpt }}
        </p>
      </a>
    </div>

    <a v-if="!isRead" @click="mark" href="javascript:" class="btn-action" title="Oznacz jako przeczytane">
      <i class="fas fa-eye"></i>
    </a>
  </div>
</template>

<script>
import {VueTimeAgo} from '../../plugins/timeago';
import store from "../../store/index";
import VueAvatar from '../avatar.vue';

export default {
  components: {
    'vue-avatar': VueAvatar,
    'vue-timeago': VueTimeAgo,
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

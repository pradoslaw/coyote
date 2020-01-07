<template>
  <li :class="{'open': isOpen}" v-on-clickaway="hideDropdown">
    <a @click.prevent="loadNotifications" href="/User/Notifications" role="button" aria-haspopup="true" aria-expanded="false">
      <span v-show="count > 0" class="badge">{{ count }}</span>

      <i class="fas fa-bell fa-fw"></i>
    </a>

    <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu right">
      <div class="dropdown-header">
        <a title="Przejdź do listy powiadomień" href="/User/Notifications">Powiadomienia</a>

        <a @click.stop="markAllAsRead" title="Oznacz jako przeczytane" href="javascript:" class="pull-right">
          <i class="far fa-calendar-check"></i>
        </a>
      </div>

      <perfect-scrollbar ref="scrollbar" class="dropdown-modal" :options="{wheelPropagation: false}">
        <div v-if="notifications === null" class="text-center">
          <i class="fas fa-spinner fa-spin"></i>
        </div>

        <vue-notification v-for="notification in notifications" :notification="notification" :key="notification.id"></vue-notification>

        <div class="text-center" v-if="Array.isArray(notifications) && notifications.length === 0">Brak powiadomień.
        </div>
      </perfect-scrollbar>
    </div>
  </li>
</template>

<script>
  import DesktopNotifications from '../../libs/notifications';
  import {default as ws} from '../../libs/realtime.js';
  import Session from '../../libs/session';
  import store from '../../store';
  import {default as PerfectScrollbar} from '../perfect-scrollbar';
  import {mixin as clickaway} from 'vue-clickaway';
  import VueNotification from './notification.vue';
  import { mapState } from 'vuex';

  export default {
    mixins: [clickaway],
    components: {
      'perfect-scrollbar': PerfectScrollbar,
      'vue-notification': VueNotification
    },
    store,
    data() {
      return {
        isOpen: false
      }
    },
    mounted() {
      this.syncCount();
      this.listenForNotification();

      this.title = document.title;
    },

    methods: {
      loadNotifications() {
        DesktopNotifications.requestPermission();
        this.isOpen = !this.isOpen;

        if (this.$store.getters['notifications/isEmpty']) {
          this.$store.dispatch('notifications/get').then(() => {
            this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-end', this.loadMoreNotifications);

            this.syncCount();
          });
        }
      },

      loadMoreNotifications() {
        this.$store.dispatch('notifications/get');
      },

      markAllAsRead() {
        this.$store.dispatch('notifications/markAll');
      },

      hideDropdown() {
        this.isOpen = false;
      },

      resetNotifications() {
        this.$refs.scrollbar.$refs.container.removeEventListener('ps-y-reach-end', this.loadMoreNotifications);
        this.isOpen = false;

        this.$store.commit('notifications/reset');
      },

      listenForNotification() {
        Session.addListener(e => {
          if (e.key === 'notifications' && e.newValue < this.count) {
            this.$store.commit('notifications/count', parseInt(e.newValue));
          }
        });

        ws.on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', data => {
          this.resetNotifications();

          this.$store.commit('notifications/increment');
          this.syncCount();

          DesktopNotifications.doNotify(data.headline, data.subject, data.url);
        });
      },

      setIcon(path) {
        const icon = document.querySelector('head link[rel="shortcut icon"]');

        icon.href = path;
      },

      setTitle(title) {
        document.title = title;
      },

      syncCount() {
        Session.setItem('notifications', this.count);
      }
    },

    watch: {
      count(value) {
        if (value > 0) {
          this.setIcon(`/img/xicon/favicon${Math.min(this.count, 6)}.png`);
          this.setTitle(`(${this.count}) ${this.title}`);
        } else {
          this.setTitle(this.title);
          this.setIcon('/img/favicon.png');
        }
      }
    },

    computed: mapState('notifications', ['notifications', 'count'])
  }
</script>

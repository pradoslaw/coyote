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
  import axios from 'axios';
  import store from '../../store';
  import Config from "../../libs/config";
  import {default as PerfectScrollbar} from '../perfect-scrollbar';
  import {mixin as clickaway} from 'vue-clickaway';
  import VueNotification from './notification.vue';

  export default {
    mixins: [clickaway],
    components: {
      'perfect-scrollbar': PerfectScrollbar,
      'vue-notification': VueNotification
    },
    store,
    data() {
      return {
        isOpen: false,
        offset: 0,
        sessionTimeout: 4 * 60 * 1000
      }
    },
    mounted() {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken();

      this.keepSessionAlive();
      this.listenForNotification();

      this.title = document.title;
    },

    beforeDestroy() {
      this.stopSessionAlive();
    },

    methods: {
      loadNotifications() {
        DesktopNotifications.requestPermission();
        this.isOpen = !this.isOpen;

        if (this.$store.getters['notifications/isEmpty']) {
          this.$store.dispatch('notifications/get').then(() => {
            this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-end', this.loadMoreNotifications);
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
          if (e.key === 'notifications' && e.newValue !== this.counter) {
            this.$store.commit('notifications/count', parseInt(e.newValue));
          }
        });

        ws.on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', data => {
          this.$store.commit('notifications/increment');
          this.resetNotifications();

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

      keepSessionAlive() {
        this.pinger = setInterval(() => axios.get('/ping'), this.sessionTimeout);
      },

      stopSessionAlive() {
        clearInterval(this.pinger);
      },
    },

    watch: {
      counter(value) {
        if (value > 0) {
          this.setIcon(`/img/xicon/favicon${Math.min(this.count, 6)}.png`);
          this.setTitle(`(${this.count}) ${this.title}`);
        } else {
          this.setTitle(this.title);
          this.setIcon('/img/favicon.png');
        }

        Session.setItem('notifications', value)
      }
    },

    computed: {
      notifications() {
        return this.$store.state.notifications.notifications;
      },
      count() {
        return this.$store.state.notifications.count;
      }
    }
  }
</script>

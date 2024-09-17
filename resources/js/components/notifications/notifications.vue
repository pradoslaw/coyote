<template>
  <li :class="{'open': isOpen}" v-click-away="hideDropdown">
    <a @click.prevent="toggleDropdown" href="/User/Notifications" class="nav-link" role="button" aria-haspopup="true" aria-expanded="false">
      <span v-show="count > 0" class="badge">{{ count }}</span>

      <i class="fas fa-bell fa-fw"></i>
    </a>

    <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu dropdown-menu-end">
      <div class="dropdown-header">
        <div v-if="unreadNotifications.length > 0" class="float-end">
          <a @click="openAll" title="Otwórz nowe w nowej karcie" href="javascript:" class="me-1">
            <i class="fas fa-up-right-from-square"></i>
          </a>

          <a @click="markAllAsRead" title="Oznacz jako przeczytane" href="javascript:">
            <i class="far fa-eye"></i>
          </a>
        </div>

        <a title="Przejdź do listy powiadomień" href="/User/Notifications">Powiadomienia</a>
      </div>

      <perfect-scrollbar ref="scrollbar" class="dropdown-modal" :options="{wheelPropagation: false}">
        <div v-if="notifications === null" class="text-center p-3">
          <i class="fas fa-spinner fa-spin"></i>
        </div>

        <vue-notification v-for="notification in notifications" :notification="notification" :key="notification.id"></vue-notification>

        <div class="text-center p-3 empty-placeholder" v-if="Array.isArray(notifications) && notifications.length === 0">
          Brak powiadomień.
        </div>
      </perfect-scrollbar>
    </div>
  </li>
</template>

<script lang="ts">
import Vue from 'vue';
import {mapGetters, mapState} from 'vuex';

import clickAway from "../../clickAway.js";
import environment from '../../environment';
import DesktopNotifications from '../../libs/notifications';
import {default as ws} from '../../libs/realtime';
import Session from '../../libs/session.js';
import store from '../../store';
import {default as PerfectScrollbar} from '../perfect-scrollbar.js';

import VueNotification from './notification.vue';

function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - base64String.length % 4) % 4);
  const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

  const rawData = window.atob(base64);
  const outputArray = new Uint8Array(rawData.length);

  for (let i = 0; i < rawData.length; ++i) {
    outputArray[i] = rawData.charCodeAt(i);
  }
  return outputArray;
}

export default Vue.extend({
  name: 'VueNotifications',
  directives: {clickAway},
  components: {
    'perfect-scrollbar': PerfectScrollbar,
    'vue-notification': VueNotification,
  },
  store,
  data() {
    return {
      title: '',
      isOpen: false,
    };
  },
  computed: {
    ...mapState('notifications', ['notifications', 'count']),
    ...mapGetters('notifications', ['unreadNotifications', 'isEmpty']),
  },
  mounted() {
    this.syncCount();
    this.listenForNotification();
    this.title = document.title;
  },
  methods: {
    toggleDropdown() {
      this.isOpen = !this.isOpen;

      if (DesktopNotifications.isSupported && DesktopNotifications.isDefault) {
        DesktopNotifications.requestPermission();
      }

      this.subscribeUser();
    },
    loadNotifications() {
      return store.dispatch('notifications/load').then(result => {
        if (!result.data.notifications.length) {
          this.removeScrollbarListener();
        }
        this.syncCount();
      });
    },
    markAllAsRead() {
      store.dispatch('notifications/markAll');
    },
    openAll() {
      this.unreadNotifications.forEach(notification => {
        window.open(`/notification/${notification.id}`);
        store.commit('notifications/mark', notification);
      });
    },
    hideDropdown() {
      this.isOpen = false;
    },
    resetNotifications() {
      this.isOpen = false;
      store.commit('notifications/reset');
      this.removeScrollbarListener();
    },
    removeScrollbarListener() {
      this.$refs.scrollbar.$refs.container.removeEventListener('ps-y-reach-end', this.loadNotifications);
    },
    listenForNotification() {
      Session.addListener(e => {
        if (e.key === 'notifications' && e.newValue < this.count) {
          store.commit('notifications/count', parseInt(e.newValue));
        }
      });

      ws.subscribe(`user:${store.state.user.user.id}`)
        .on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', data => {
          this.resetNotifications();
          store.commit('notifications/increment');
          this.syncCount();
          DesktopNotifications.notify(data.headline, data.subject, data.url);
        })
        .on('NotificationRead', () => store.commit('notifications/decrement'));
    },
    subscribeUser() {
      if (!('PushManager' in window) || !('serviceWorker' in navigator)) {
        return;
      }

      navigator.serviceWorker.ready.then(registration => {
        const serverKey = urlBase64ToUint8Array(environment.vapidKey);

        return registration.pushManager.subscribe({
          userVisibleOnly: true,
          applicationServerKey: serverKey,
        });
      })
        .catch(() => console.log('Push notification: access denied.'))
        .then(pushSubscription => pushSubscription && store.dispatch('user/pushSubscription', pushSubscription));
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
    },
  },
  watch: {
    count(value) {
      if (value > 0) {
        this.setTitle(`(${this.count}) ${this.title}`);
      } else {
        this.setTitle(this.title);
      }
    },
    isOpen(isOpen) {
      if (isOpen) {
        if (this.notifications === null) {
          this.loadNotifications();
        }

        this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-end', this.loadNotifications);
      } else {
        this.removeScrollbarListener();
      }
    },
  },
});
</script>

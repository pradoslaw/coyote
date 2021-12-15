<template>
  <li :class="{'open': isOpen}" v-on-clickaway="hideDropdown">
    <a @click.prevent="toggleDropdown" href="/User/Notifications" class="nav-link" role="button" aria-haspopup="true" aria-expanded="false">
      <span v-show="count > 0" class="badge">{{ count }}</span>

      <i class="fas fa-bell fa-fw"></i>
    </a>

    <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu dropdown-menu-right">
      <div class="dropdown-header">
        <div class="float-right">
          <a v-if="unreadNotifications.length > 0" @click="openAll" title="Otwórz nowe w nowej karcie" href="javascript:" class="mr-1">
            <i class="fas fa-external-link-alt"></i>
          </a>

          <a @click="markAllAsRead" title="Oznacz jako przeczytane" href="javascript:">
            <i class="far fa-eye"></i>
          </a>
        </div>

        <a title="Przejdź do listy powiadomień" href="/User/Notifications">Powiadomienia</a>
      </div>

      <perfect-scrollbar ref="scrollbar" class="dropdown-modal" :options="{wheelPropagation: false}">
        <div v-if="notifications === null" class="text-center">
          <i class="fas fa-spinner fa-spin"></i>
        </div>

        <vue-notification v-for="notification in notifications" :notification="notification" :key="notification.id"></vue-notification>

        <div class="text-center" v-if="Array.isArray(notifications) && notifications.length === 0">Brak powiadomień.</div>
      </perfect-scrollbar>
    </div>
  </li>
</template>

<script>
  import DesktopNotifications from '../../libs/notifications';
  import {default as ws} from '../../libs/realtime.ts';
  import Session from '../../libs/session';
  import store from '../../store';
  import {default as PerfectScrollbar} from '../perfect-scrollbar';
  import {mixin as clickaway} from 'vue-clickaway';
  import VueNotification from './notification.vue';
  import { mapState, mapGetters } from 'vuex';
  import environment from '@/environment';

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
      toggleDropdown() {
        this.isOpen = !this.isOpen;

        if (DesktopNotifications.isSupported && DesktopNotifications.isDefault) {
          DesktopNotifications.requestPermission();
        }

        this.subscribeUser();
      },

      loadNotifications() {
        return store.dispatch('notifications/load').then(result => {
          // no more new notifications? remove listener to avoid infinite loop
          if (!result.data.notifications.length) {
            this.removeScrollbarListener();
          }

          // sync unread notifications counter with other tabs
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
          // notification link was clicked in email or desktop notifications.
          .on('NotificationRead', () => store.commit('notifications/decrement'));
      },

      subscribeUser() {
        if (!('PushManager' in window) || !navigator.serviceWorker?.ready) {
          return;
        }

        navigator.serviceWorker.ready.then(registration => {
            const serverKey = urlBase64ToUint8Array(environment.vapidKey);

            return registration.pushManager.subscribe({
              userVisibleOnly: true,
              applicationServerKey: serverKey
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
      },

      isOpen(isOpen) {
        if (isOpen) {
          this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-end', this.loadNotifications);
        }
        else {
          // we must remove listener after closing a list. I don't know why but scrollbar event works even after
          // hiding the list
          this.removeScrollbarListener();
        }
      }
    },

    computed: {
      ...mapState('notifications', ['notifications', 'count']),
      ...mapGetters('notifications', ['unreadNotifications', 'isEmpty'])
    }
  }
</script>

<template>
    <li :class="{'open': isOpen}">
        <a @click.prevent="loadNotifications" href="/User/Notifications" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <span v-show="counter > 0" class="badge">{{ counter }}</span>

            <i class="fas fa-bell fa-fw"></i>
        </a>

        <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu right">
            <div class="dropdown-header">
                <a title="Przejdź do listy powiadomień" href="/User/Notifications">Powiadomienia</a>

                <a @click.prevent="markAllAsRead" title="Oznacz jako przeczytane" href="/User/Notifications/Mark" class="pull-right">
                    <i class="far fa-calendar-check"></i>
                </a>
            </div>

            <perfect-scrollbar class="dropdown-modal" :options="{wheelPropagation: false}" @ps-y-reach-end="loadMoreNotifications">
                <div v-for="notification in notifications" :class="{'unread': ! notification.is_read}" class="notification">
                    <a @click.prevent="showNotification(notification)" @mousedown="markAsRead(notification)" :href="notification.url" :title="notification.headline" class="notification-link">
                        <div class="media">
                            <div class="media-left">
                                <img src="/img/avatar.png">
                                <!--                            <img :src="notification.photo">-->
                            </div>

                            <div class="media-body">
                                <header>
                                    <h4>{{ notification.headline }}</h4>
                                    <small>{{ notification.created_at }}</small>
                                </header>

                                <h3>{{ notification.subject }}</h3>
                                <p>{{ notification.excerpt }}</p>
                            </div>
                        </div>
                    </a>

                    <a @click.stop="deleteNotification(notification)" href="javascript:" class="btn-delete-alert" title="Usuń">
                        <i class="fas fa-times"></i>
                    </a>
                </div>
            </perfect-scrollbar>
        </div>
    </li>
</template>

<script>
    import DesktopNotifications from '../libs/notifications';
    import { default as ws } from '../libs/realtime.js';
    import Session from '../libs/session';
    import axios from 'axios';
    import Config from "../libs/config";
    import { PerfectScrollbar } from 'vue2-perfect-scrollbar';

    export default {
        components: {
            PerfectScrollbar
        },
        props: {
            counter: {
                type: Number
            }
        },
        data() {
            return {
                isOpen: false,
                notifications: [],
                offset: 0
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

                if (this.$refs.dropdown.style.display === 'none') {
                    axios.get('/User/Notifications/Ajax').then(result => {
                        this.notifications = result.data.notifications;
                        this.counter = result.data.unread;
                        this.offset = result.data.offset;
                    });
                }
            },

            loadMoreNotifications() {
                if (this.isOngoing) {
                    return;
                }

                this.isOngoing = true;

                axios.get('/User/Notifications/Ajax', {params: {offset: this.offset}})
                    .then(result => {
                        this.notifications = Object.assign(this.notifications, result.data.notifications);
                        this.offset = result.data.offset;
                    })
                    .finally(() => {
                        this.isOngoing = false;
                    });
            },

            showNotification(notification) {
                window.location.href = `/notification/${notification.guid}`;
            },

            deleteNotification(notification) {
                axios.post(`/User/Notifications/Delete/${notification.id}`);

                const index = this.notifications.findIndex(item => item.id === notification.id);

                this.$delete(this.notifications, index);
            },

            markAllAsRead() {
                axios.post('/User/Notifications/Mark');

                this.notifications.forEach(notification => {
                    notification.is_read = true;
                });
            },

            markAsRead(notification) {
                notification.is_read = true;
            },

            listenForNotification() {
                Session.addListener(e => {
                    if (e.key === 'notifications' && e.newValue !== this.counter) {
                        this.counter = parseInt(e.newValue);
                    }
                });

                ws.on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', data => {
                    this.counter += 1;

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
                this.pinger = setInterval(() => axios.get('/ping'), 30000);
            },

            stopSessionAlive() {
                clearInterval(this.pinger);
            },
        },

        watch: {
            counter: function(value) {
                if (value > 0) {
                    this.setIcon(`/img/xicon/favicon${Math.min(this.counter, 6)}.png`);
                    this.setTitle(`(${this.counter}) ${this.title}`);
                }
                else {
                    this.setTitle(this.title);
                    this.setIcon('/img/favicon.png');
                }

                Session.setItem('notifications', value)
            }
        }
    }
</script>

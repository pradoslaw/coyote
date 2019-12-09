<template>
    <li :class="{'open': isOpen}" class="btn-alerts">
        <a @click.prevent="loadNotifications" href="/User/Notifications" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <span v-show="counter > 0" class="badge">{{ counter }}</span>

            <i class="fas fa-bell fa-fw"></i>
        </a>

        <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu right">
            <div class="dropdown-header">
                <a title="Przejdź do listy powiadomień" href="/User/Notifications">Powiadomienia</a>

                <a @click.prevent="asRead" title="Oznacz jako przeczytane" href="/User/Notifications/Mark">
                    <i class="far fa-calendar-check"></i>
                </a>
            </div>
            <div class="dropdown-modal">
                <ul>
                    <li v-for="notification in notifications" :class="{'unread': ! notification.is_read}">
                        <a @click.prevent="showNotification(notification)" :href="notification.url" :title="notification.headline">
                            <img :src="notification.photo">

                            <div>
                                <header>
                                    <h4>{{ notification.headline }}</h4>
                                    <small>{{ notification.created_at }}</small>
                                </header>

                                <h3>{{ notification.subject }}</h3>
                                <p>{{ notification.excerpt }}</p>
                            </div>
                        </a>

                        <a @click.prevent="deleteNotification(notification)" class="btn-delete-alert" title="Usuń">
                            <i class="fas fa-times"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </li>
</template>

<script>
    import DesktopNotifications from '../libs/notifications';
    import { default as ws } from '../libs/realtime.js';
    import Session from '../libs/session';
    import axios from 'axios';
    import Config from "../libs/config";

    export default {
        props: {
            counter: {
                type: Number
            }
        },
        data() {
            return {
                isOpen: false,
                notifications: []
            }
        },
        mounted() {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken();

            this.startPing();
            this.listenForNotification();

            this.title = document.title;
        },

        beforeDestroy() {
            this.stopPing();
        },

        methods: {
            loadNotifications() {
                DesktopNotifications.requestPermission();

                if (this.$refs.dropdown.style.display === 'none') {
                    axios.get('/User/Notifications/Ajax').then(result => {
                        this.notifications = result.data.notifications;
                    });
                }
            },

            showNotification(notification) {
                window.location.href = `/notification/${notification.guid}`;
            },

            deleteNotification(notification) {
                axios.post(`/User/Notifications/Delete/${notification.id}`);

                notification = {};
            },

            asRead() {
                axios.post('/User/Notifications/Mark');

                this.notifications.forEach(notification => {
                    notification.is_read = true;
                });
            },

            listenForNotification() {
                Session.addListener(function (e) {
                    if (e.key === 'notifications' && e.newValue !== this.counter) {
                        this.counter = e.newValue;
                    }
                });

                ws.on('Illuminate\\Notifications\\Events\\BroadcastNotificationCreated', data => {
                    this.counter += 1;

                    DesktopNotifications.doNotify(data.headline, data.subject, data.url);

                    // ugly hack to firefox: store counter in session storage with a lille bit of delay.
                    // if we have two open tabs both with websocket connection, then each tab will receive it's own
                    // notification. saving counter in local storage will call session listener (see above).
                    // make a long story short: without setTimeout() one notification will be shown as two if user
                    // has two open tabs.
                    setTimeout(() => Session.setItem('notifications', this.counter), 500);
                });
            },

            setFavIcon(path) {
                const icon = document.querySelector('head link[rel="shortcut icon"]');

                icon.innerHTML = path;
            },

            startPing() {
                // this.pinger = setInterval(() => axios.get('/ping'), 30000);
            },

            stopPing() {
                // clearInterval(this.pinger);
            },
        },

        watch: {
            counter(value) {
                if (value > 0) {
                    this.setFavIcon(`/img/xicon/favicon${Math.min(this.counter, 6)}.png`);
                }
                else {
                    document.title = this.title;
                    this.setFavIcon('/img/favicon.png');
                }
            }
        }
    }
</script>

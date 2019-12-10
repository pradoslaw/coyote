<template>
    <li :class="{'open': isOpen}" class="btn-messages">
        <a @click.prevent="loadPms" href="/User/Pm" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
            <span v-show="counter > 0" class="badge">{{ counter }}</span>

            <i class="fas fa-envelope fa-fw"></i>
        </a>

        <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu right">
            <div class="dropdown-header">
                <a title="Przejdź do listy wiadomości" href="/User/Pm">Wiadomości</a>

                <a id="btn-write-message"  href="/User/Pm/Submit">
                    Wyślij wiadomość
                </a>
            </div>

            <perfect-scrollbar class="dropdown-modal" :options="{wheelPropagation: false}">
                <ul>
                    <li v-for="item in pm" :title="pm.text" :class="{'unread': ! item.read_at}">
                        <a :href="item.url">
                            <div>
                                <header>
                                    <h4>{{ item.name }}</h4>
                                    <small>{{ item.created_at }}</small>
                                </header>

                                <p>
                                    <template v-if="item.folder === SENTBOX">
                                        <i v-if="item.read_at" class="fas fa-check"></i>
                                        <span v-else>Ty: </span>
                                    </template>

                                    {{ item.text }}
                                </p>
                            </div>
                        </a>
                    </li>
                </ul>
            </perfect-scrollbar>
        </div>
    </li>
</template>

<script>
    import DesktopNotifications from '../libs/notifications';
    import { default as ws } from '../libs/realtime.js';
    import axios from 'axios';
    import Config from '../libs/config';
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
                SENTBOX: 2,
                isOpen: false,
                pm: []
            }
        },
        mounted() {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken();

            this.listenForPm();
        },
        methods: {
            loadPms() {
                if (this.$refs.dropdown.style.display === 'none') {
                    axios.get('/User/Pm/Ajax').then(result => {
                        this.pm = result.data.pm;
                    });
                }
            },

            listenForPm() {
                ws.on('pm', data => {
                    this.counter += 1;

                    DesktopNotifications.doNotify(data.senderName, data.excerpt, '#top');
                });
            },
        },
    };
</script>

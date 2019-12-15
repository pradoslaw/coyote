<template>
  <li :class="{'open': isOpen}" v-on-clickaway="hideDropdown">
    <a @click.prevent="loadMessages" href="/User/Pm" role="button" aria-haspopup="true" aria-expanded="false">
      <span v-show="counter > 0" class="badge">{{ counter }}</span>

      <i class="fas fa-envelope fa-fw"></i>
    </a>

    <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu right">
      <div class="dropdown-header">
        <a title="Przejdź do listy wiadomości" href="/User/Pm">Wiadomości</a>

        <a class="btn-write-message" href="/User/Pm/Submit">
          Wyślij wiadomość
        </a>
      </div>

      <perfect-scrollbar class="dropdown-modal" :options="{wheelPropagation: false}">
        <div v-if="messages === null" class="text-center">
          <i class="fas fa-spinner fa-spin"></i>
        </div>

        <vue-message v-for="message in messages" :message="message" :key="message.id"></vue-message>

        <div class="text-center" v-if="Array.isArray(messages) && messages.length === 0">Brak wiadomości prywatnych.</div>
      </perfect-scrollbar>
    </div>
  </li>
</template>

<script>
  import DesktopNotifications from '../../libs/notifications';
  import {default as ws} from '../../libs/realtime.js';
  import axios from 'axios';
  import Config from '../../libs/config';
  import {default as PerfectScrollbar} from '../perfect-scrollbar';
  import {mixin as clickaway} from 'vue-clickaway';
  import VueMessage from './message-compact.vue';

  export default {
    mixins: [clickaway],
    components: {
      'perfect-scrollbar': PerfectScrollbar,
      'vue-message': VueMessage
    },
    props: {
      counter: {
        type: Number
      }
    },
    data() {
      return {
        isOpen: false,
        messages: null // initial value must be null to show fa-spinner
      }
    },
    mounted() {
      axios.defaults.headers.common['X-CSRF-TOKEN'] = Config.csrfToken();

      this.listenForMessages();
    },
    methods: {
      loadMessages() {
        this.isOpen = !this.isOpen;

        if (this.messages === null) {
          axios.get('/User/Pm/Ajax').then(result => {
            this.messages = result.data.pm;
          });
        }
      },

      hideDropdown() {
        this.isOpen = false;
      },

      listenForMessages() {
        ws.on('Coyote\\Events\\PmCreated', data => {
          this.counter += 1;
          this.isOpen = false;
          this.messages = null;

          DesktopNotifications.doNotify(data.user.name, data.excerpt, data.url);
        });
      },
    }
  };
</script>

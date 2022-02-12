<template>
  <li :class="{'open': isOpen}" v-on-clickaway="hideDropdown" class="nav-item">
    <a @click.prevent="loadMessages" href="/User/Pm" class="nav-link" role="button" aria-haspopup="true" aria-expanded="false">
      <span v-show="count > 0" class="badge">{{ count }}</span>

      <i class="fas fa-envelope fa-fw"></i>
    </a>

    <div ref="dropdown" v-show="isOpen" class="dropdown-alerts dropdown-menu dropdown-menu-right">
      <div class="dropdown-header">
        <a class="float-right small" href="/User/Pm/Submit">
          Wyślij wiadomość
        </a>

        <a title="Przejdź do listy wiadomości" href="/User/Pm">Wiadomości</a>
      </div>

      <perfect-scrollbar class="dropdown-modal" :options="{wheelPropagation: false, useBothWheelAxes: false, suppressScrollX: true}">
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
  import { default as ws } from '../../libs/realtime.ts';
  import store from '../../store';
  import { default as PerfectScrollbar } from '../perfect-scrollbar';
  import { mixin as clickaway } from 'vue-clickaway';
  import VueMessage from './message-compact.vue';
  import { mapState, mapGetters, mapMutations } from "vuex";

  export default {
    mixins: [ clickaway ],
    components: {
      'perfect-scrollbar': PerfectScrollbar,
      'vue-message': VueMessage
    },
    store,
    data() {
      return {
        isOpen: false,
        currentTitle: null,
        animationId: null
      }
    },
    mounted() {
      this.listenForMessages();
      this.listenForVisibilityChange();
    },
    methods: {
      loadMessages() {
        this.isOpen = !this.isOpen;

        if (this.isEmpty) {
          store.dispatch('inbox/get');
        }
      },

      hideDropdown() {
        this.isOpen = false;
      },

      listenForMessages() {
        this.channel.on('PmCreated', ({ count, data }) => {
          this.init(count);
          this.reset();

          this.isOpen = false;

          DesktopNotifications.notify(data.user.name, data.excerpt, data.url);

          this.startAnimation(data.user);
        });
      },

      listenForVisibilityChange() {
        document.addEventListener('visibilitychange', () => {
          if (!document.hidden) {
            this.stopAnimation();
          }
        });

        this.channel.on('PmVisible', this.stopAnimation);
      },

      startAnimation(user) {
        if (!document.hidden) {
          // page is not hidden. tell other tabs to stop animation
          this.stopAnimationOnAllWindows();

          return;
        }

        // there is an animation still in progress. skip it.
        if (this.animationId !== null) {
          return;
        }

        this.currentTitle = document.title;

        this.animationId = setInterval(() =>
          document.title = document.title === this.currentTitle ? 'Masz wiadomość od: ' + user.name : this.currentTitle, 2000
        );
      },

      stopAnimation() {
        if (this.animationId === null) {
          return;
        }

        // remove animation if exists
        clearInterval(this.animationId);
        // restore original title
        document.title = this.currentTitle;

        this.currentTitle = this.animationId = null;

        this.stopAnimationOnAllWindows();
      },

      stopAnimationOnAllWindows() {
        // send event to other tabs
        this.channel.whisper('PmVisible', {});
      },

      ...mapMutations('inbox', ['init', 'reset'])
    },
    computed: {
      ...mapState('inbox', ['messages', 'count']),
      ...mapGetters('inbox', ['isEmpty']),

      channel() {
        return ws.subscribe(`user:${store.state.user.user.id}`);
      }
    }
  };
</script>

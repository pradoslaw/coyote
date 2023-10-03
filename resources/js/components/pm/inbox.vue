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
        <div v-if="messages === null" class="text-center p-3">
          <i class="fas fa-spinner fa-spin"></i>
        </div>

        <vue-message v-for="message in messages" :message="message" :key="message.id"></vue-message>

        <div class="text-center p-3" v-if="Array.isArray(messages) && messages.length === 0">Brak wiadomości prywatnych.</div>
      </perfect-scrollbar>
    </div>
  </li>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import DesktopNotifications from '../../libs/notifications';
  import { default as ws } from '../../libs/realtime';
  import { default as PerfectScrollbar } from '../perfect-scrollbar';
  import { mixin as clickaway } from 'vue-clickaway';
  import VueMessage from './message-compact.vue';
  import { mapState, mapGetters, mapMutations, mapActions } from "vuex";
  import { Message, User } from "@/types/models";
  import store from "@/store";

  @Component({
    mixins: [ clickaway ],
    components: {
      'perfect-scrollbar': PerfectScrollbar,
      'vue-message': VueMessage
    },
    methods: {
      ...mapMutations('inbox', ['SET_COUNT', 'RESET_MESSAGE', 'MARK']),
      ...mapActions('inbox', ['get']),
    },
    computed: {
      ...mapState('inbox', ['messages', 'count']),
      ...mapState('user', ['user']),
      ...mapGetters('inbox', ['isEmpty']),
    }
  })
  export default class VueInbox extends Vue {
    isOpen = false;
    currentTitle: string | null = null;
    animationId: null | NodeJS.Timer = null;
    count!: number;
    messages!: Message[];
    isEmpty!: boolean;
    user!: User;
    SET_COUNT!: (count: number) => void;
    RESET_MESSAGE!: () => void;
    MARK!: (message: Message) => void;
    get!: () => any;

    mounted() {
      this.listenForMessages();
      this.listenForVisibilityChange();
    }

    loadMessages() {
      this.isOpen = !this.isOpen;

      if (this.isEmpty) {
        this.get();
      }
    }

    hideDropdown() {
      this.isOpen = false;
    }

    listenForMessages() {
      this.channel.on('PmCreated', ({ count, data }) => {
        this.SET_COUNT(count);
        this.RESET_MESSAGE();

        this.isOpen = false;

        DesktopNotifications.notify(data.user.name, data.excerpt, data.url);

        this.startAnimation(data.user);
      });

      this.channel.on('PmRead', data => {
        if (this.count > 0) {
          this.SET_COUNT(this.count - 1);
        }

        this.stopAnimation();

        if (!this.messages) {
          return;
        }

        const message = this.messages.find(item => item.text_id === data.text_id);

        if (message) {
          this.MARK(message);
        }
      });
    }

    listenForVisibilityChange() {
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
          this.stopAnimation();
        }
      });

      this.channel.on('PmVisible', this.stopAnimation);
    }

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
        document.title = document.title === this.currentTitle ? 'Masz wiadomość od: ' + user.name : this.currentTitle as string, 2000
      );
    }

    stopAnimation() {
      if (this.animationId === null) {
        return;
      }

      // remove animation if exists
      clearInterval(this.animationId);
      // restore original title
      document.title = this.currentTitle as string;

      this.currentTitle = this.animationId = null;

      this.stopAnimationOnAllWindows();
    }

    stopAnimationOnAllWindows() {
      // send event to other tabs
      this.channel.whisper('PmVisible', {});
    }

    get channel() {
      return ws.subscribe(`user:${this.user.id}`);
    }
  }
</script>

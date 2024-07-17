<template>
  <div :class="{'sequential': message.sequential, 'unread': ! isRead}" class="media">
    <a v-if="!message.sequential" v-profile="message.user.id" class="i-45 me-2 d-none d-sm-block">
      <vue-avatar :photo="message.user.photo" :name="message.user.name" class="mw-100"></vue-avatar>
    </a>

    <div class="media-body">
      <template v-if="!message.sequential">
        <small class="float-end text-muted">
          <vue-timeago :datetime="message.created_at"></vue-timeago>
        </small>

        <h3>
          <vue-username v-if="clickableText" :user="message.user" :href="'/User/Pm/Show/' + message.id"></vue-username>
          <vue-username v-else :user="message.user"></vue-username>
        </h3>
      </template>

      <a @click="deleteMessage" class="btn-delete float-end text-danger" href="javascript:" title="Usuń">
        <i class="fas fa-trash-can"></i>
      </a>

      <a v-if="clickableText" :href="'/User/Pm/Show/' + message.id" class="excerpt">{{ message.excerpt ? message.excerpt : '(kliknij, aby przeczytać)' }}</a>
      <div v-else class="pm-text" v-html="message.text"></div>

      <small v-if="last && message.folder === SENTBOX && message.read_at" class="text-muted">
        <i class="fas fa-check"></i>
        Przeczytano,
        <vue-timeago :datetime="message.read_at"></vue-timeago>
      </small>
    </div>
  </div>
</template>

<script lang="ts">
import VueUserName from '@/components/user-name.vue';
import VueTimeago from '@/plugins/timeago.js';
import {MessageFolder} from '@/types/models';
import Vue from 'vue';
import VueAvatar from '../avatar.vue';
import {default as mixins} from '../mixins/user.js';

Vue.use(VueTimeago);

export default Vue.extend({
  name: 'VueMessage',
  mixins: [mixins],
  components: {
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
  },
  props: {
    message: {
      type: Object,
      required: true,
    },
    last: {
      type: Boolean,
      default: false,
    },
    clickableText: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      SENTBOX: MessageFolder.sentbox,
    };
  },
  methods: {
    deleteMessage() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć tę wiadomość?',
        okLabel: 'Tak, usuń',
      }).then(() =>
        this.$store.dispatch(`messages/${this.clickableText ? 'trash' : 'remove'}`, this.message),
      );
    },
  },
  computed: {
    isRead() {
      return this.message.folder !== MessageFolder.sentbox ? this.message.read_at !== null : true;
    },
    excerpt() {
      return this.clickableText ? (this.message.excerpt ? this.message.excerpt : '(kliknij, aby przeczytać)') : this.message.text;
    },
  },
});
</script>

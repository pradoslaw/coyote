<template>
  <div :class="{'sequential': message.sequential, 'unread': ! isRead}" class="media">
    <a v-if="!message.sequential" v-profile="message.user.id" class="i-45 mr-2 d-none d-sm-block">
      <vue-avatar :photo="message.user.photo" :name="message.user.name" class="mw-100"></vue-avatar>
    </a>

    <div class="media-body">
      <template v-if="!message.sequential">
        <small class="float-right text-muted"><vue-timeago :datetime="message.created_at"></vue-timeago></small>

        <h3>
          <vue-username v-if="clickableText" :user="message.user" :href="'/User/Pm/Show/' + message.id"></vue-username>
          <vue-username v-else :user="message.user"></vue-username>
        </h3>
      </template>

      <a @click="deleteMessage(true)" class="btn-delete float-right text-danger" href="javascript:" title="Usuń">
        <i class="fas fa-times"></i>
      </a>

      <a v-if="clickableText" :href="'/User/Pm/Show/' + message.id" class="excerpt">{{ message.excerpt ? message.excerpt : '(kliknij, aby przeczytać)' }}</a>
      <div v-else class="pm-text" v-html="message.text"></div>

      <small v-if="last && message.folder === SENTBOX && message.read_at" class="text-muted"><i class="fas fa-check"></i> Przeczytano, <vue-timeago :datetime="message.read_at"></vue-timeago></small>
    </div>

    <vue-modal ref="confirm">
      Czy na pewno chcesz usunąć tę wiadomość?

      <template slot="buttons">
        <button @click="$refs.confirm.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
        <button @click="deleteMessage(false)" type="submit" class="btn btn-danger danger">Tak, usuń</button>
      </template>
    </vue-modal>
  </div>
</template>

<script>
  import { default as mixins } from '../mixins/user';
  import VueModal from '../modal.vue';
  import VueAvatar from '../avatar.vue';
  import VueTimeago from '../../plugins/timeago';
  import VueUserName from '@/components/user-name';

  Vue.use(VueTimeago);

  export default {
    mixins: [ mixins ],
    components: { 'vue-modal': VueModal, 'vue-avatar': VueAvatar, 'vue-username': VueUserName },
    props: {
      message: {
        type: Object
      },
      last: {
        type: Boolean,
        default: false
      },
      clickableText: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        SENTBOX: 2
      }
    },
    methods: {
      deleteMessage(confirm) {
        if (confirm) {
          this.$refs.confirm.open();
        } else {
          this.$refs.confirm.close();

          this.$store.dispatch(`messages/${this.clickableText ? 'trash': 'remove' }`, this.message);
        }
      }
    },
    computed: {
      isRead() {
        return this.message.folder !== this.SENTBOX ? (this.message.read_at !== null) : true;
      },

      excerpt() {
        return this.clickableText ? (message.excerpt ? message.excerpt : '(kliknij, aby przeczytać') : message.text
      }
    }
  }
</script>

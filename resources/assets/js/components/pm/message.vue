<template>
  <div class="media" :class="{'unread': ! isRead}" @mouseenter.once="asRead">

      <a v-profile="message.user.id" class="d-inline-block">
        <object :data="message.user.photo || '//'" type="image/png" class="media-object mr-2">
          <img src="/img/avatar.png" :alt="message.user.name">
        </object>
      </a>

    <div class="media-body">
      <small class="float-right">{{ message.created_at }}</small>

      <h3>
        <a v-if="clickableText" :href="'/User/Pm/Show/' + message.id">{{ message.user.name }}</a>
        <a v-else v-profile="message.user.id">{{ message.user.name }}</a>
      </h3>

      <a @click="deleteMessage(true)" class="btn-delete-pm float-right text-danger" href="javascript:" title="Usuń">
        <i class="fas fa-times"></i>
      </a>

      <a v-if="clickableText" :href="'/User/Pm/Show/' + message.id" class="excerpt">{{ message.excerpt ? message.excerpt : '(kliknij, aby przeczytać)' }}</a>
      <div v-else class="pm-text" v-html="message.text"></div>

      <small v-if="last && message.folder === SENTBOX && message.read_at" class="text-muted"><i class="fas fa-check"></i> Przeczytano, {{ message.read_at }}</small>
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

  export default {
    mixins: [ mixins ],
    components: {'vue-modal': VueModal},
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
      },

      asRead() {
        if (this.isRead || this.clickableText) {
          return;
        }

        this.$store.dispatch('messages/mark', this.message).then(() => this.$store.commit('inbox/decrement'));
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

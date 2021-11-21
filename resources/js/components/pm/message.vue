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

      <a @click="deleteMessage" class="btn-delete float-right text-danger" href="javascript:" title="Usuń">
        <i class="fas fa-times"></i>
      </a>

      <a v-if="clickableText" :href="'/User/Pm/Show/' + message.id" class="excerpt">{{ message.excerpt ? message.excerpt : '(kliknij, aby przeczytać)' }}</a>
      <div v-else class="pm-text" v-html="message.text"></div>

      <small v-if="last && message.folder === SENTBOX && message.read_at" class="text-muted"><i class="fas fa-check"></i> Przeczytano, <vue-timeago :datetime="message.read_at"></vue-timeago></small>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import {default as mixins} from '../mixins/user';
  import VueAvatar from '../avatar.vue';
  import VueTimeago from '@/plugins/timeago';
  import VueUserName from '@/components/user-name.vue';
  import {Prop} from 'vue-property-decorator';
  import Component from 'vue-class-component';
  import {Message, MessageFolder} from '@/types/models';

  Vue.use(VueTimeago);

  @Component({
    mixins: [ mixins ],
    components: { 'vue-avatar': VueAvatar, 'vue-username': VueUserName }
  })
  export default class VueMessage extends Vue {
    @Prop()
    message!: Message;

    @Prop({default: false})
    last!: boolean;

    @Prop({default: false})
    clickableText!: boolean;

    SENTBOX = MessageFolder.sentbox;

    deleteMessage() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć tę wiadomość?',
        okLabel: 'Tak, usuń'
      })
      .then(() => this.$store.dispatch(`messages/${this.clickableText ? 'trash': 'remove' }`, this.message));
    }

    get isRead() {
      return this.message.folder !== MessageFolder.sentbox ? (this.message.read_at !== null) : true;
    }

    get excerpt() {
      return this.clickableText ? (this.message.excerpt ? this.message.excerpt : '(kliknij, aby przeczytać') : this.message.text
    }
  }
</script>

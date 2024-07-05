<template>
  <div :id="anchor" :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false, 'border border-danger': comment.deleted_at}" class="media">
    <div class="mr-2">
      <a v-profile="comment.user.id">
        <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="i-35 d-block img-thumbnail"></vue-avatar>
      </a>
    </div>

    <div class="media-body d-flex">
      <vue-comment-form
        v-if="comment.is_editing"
        :microblog="comment"
        ref="form"
        class="w-100 mr-1"
        @cancel="edit(comment)"
        @save="edit(comment)"
      />

      <div v-if="!comment.is_editing" class="w-100">
        <vue-username :user="comment.user"></vue-username>

        <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"></vue-flag>

        <div class="comment-text" v-html="comment.html"></div>

        <ul class="d-none d-sm-block list-inline list-inline-bullet-sm microblog-comment-list small m-0">
          <li class="list-inline-item">
            <a :href="comment.url">
              <vue-timeago :datetime="comment.created_at"></vue-timeago>
            </a>
          </li>

          <li class="list-inline-item">
            <a
              @click="checkAuth(vote, comment)"
              @mouseenter.once="loadVoters(comment)"
              :aria-label="commentVoters"
              :class="{'vote-active': comment.is_voted}"
              href="javascript:"
              data-balloon-pos="up"
              data-balloon-break>
              {{ commentLabel }}
            </a>
          </li>

          <li class="list-inline-item">
            <a @click="checkAuth(reply)" href="javascript:">Odpowiedz</a>
          </li>

          <li v-if="isAuthorized" class="list-inline-item">
            <a href="javascript:" :data-metadata="comment.metadata" :data-url="comment.url">Zgłoś</a>
          </li>
        </ul>

        <ul class="d-sm-none list-inline text-muted small m-0">
          <li class="list-inline-item">
            <a :href="comment.url" class="text-muted">
              <vue-timeago :datetime="comment.created_at"></vue-timeago>
            </a>
          </li>

          <li class="list-inline-item">
            <a href="#" class="text-muted" data-bs-toggle="dropdown">
              <i class="fa fa-bars"></i>
            </a>

            <div class="dropdown-menu">
              <a @click="checkAuth(reply)" href="javascript:" class="dropdown-item text-muted">Odpowiedz</a>

              <a @click="checkAuth(vote, comment)"
                 :title="commentVoters"
                 :class="{'text-primary': comment.is_voted, 'text-muted': !comment.is_voted}"
                 href="javascript:"
                 class="dropdown-item"
              >
                {{ commentLabel }}
              </a>
            </div>
          </li>
        </ul>
      </div>

      <div v-if="isAuthorized" class="dropdown">
        <button class="btn btn-xs border-0 text-muted" type="button" data-bs-toggle="dropdown" aria-label="Dropdown"><i class="small fa fa-ellipsis"></i></button>

        <div class="dropdown-menu dropdown-menu-right">
          <template v-if="comment.permissions.update">

            <template v-if="!comment.deleted_at">
              <a @click="edit(comment)" class="dropdown-item btn-sm-edit" href="javascript:"><i class="fas fa-pen-to-square fa-fw"></i> Edytuj</a>
              <a @click="deleteItem" class="dropdown-item btn-sm-remove" href="javascript:"><i class="fas fa-trash-can fa-fw"></i> Usuń</a>
            </template>

            <a v-else @click="restoreItem" class="dropdown-item" href="javascript:"><i class="fas fa-trash-arrow-up fa-fw"></i> Przywróć</a>

            <div v-if="comment.user.id !== user.id" class="dropdown-divider"></div>
          </template>

          <a v-if="comment.user.id !== user.id" @click="block(comment.user)" href="javascript:" class="dropdown-item">
            <i class="fas fa-fw fa-user-slash"></i>
            Zablokuj użytkownika
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import declination from '@/libs/declination';
import VueTimeago from '@/plugins/timeago';
import store from "@/store";
import {Microblog} from "@/types/models";
import Vue from 'vue';
import Component from "vue-class-component";
import {mixin as clickaway} from "vue-clickaway";
import {Mixins, Prop} from "vue-property-decorator";
import {mapActions, mapGetters, mapState} from "vuex";
import VueAvatar from '../avatar.vue';
import VueFlag from '../flags/flag.vue';
import {MicroblogMixin} from "../mixins/microblog";
import {default as mixins} from '../mixins/user';
import VueUserName from '../user-name.vue';
import VueCommentForm from "./comment-form.vue";

Vue.use(VueTimeago);

@Component({
  name: 'comment',
  mixins: [clickaway, mixins],
  store,
  components: {
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
  },
  computed: {
    ...mapState('user', ['user']),
    ...mapGetters('user', ['isAuthorized']),
  },
  methods: {
    ...mapActions('microblogs', ['vote', 'loadVoters']),
  },
})
export default class VueComment extends Mixins(MicroblogMixin) {
  @Prop(Object)
  comment!: Microblog;

  reply() {
    this.$emit('reply', this.comment.user);
  }

  deleteItem() {
    this.delete('microblogs/deleteComment', this.comment);
  }

  restoreItem() {
    store.dispatch('microblogs/restoreComment', this.comment);
  }

  get anchor() {
    return `comment-${this.comment.id}`;
  }

  get highlight() {
    return '#' + this.anchor === window.location.hash;
  }

  get commentLabel() {
    return this.comment.votes + ' ' + declination(this.comment.votes, ['głos', 'głosy', 'głosów']);
  }

  get commentVoters() {
    return this.splice(this.comment.voters);
  }

  get flags() {
    return store.getters['flags/filter'](this.comment.id, 'Coyote\\Microblog');
  }
}
</script>

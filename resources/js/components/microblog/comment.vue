<template>
  <div :id="anchor" :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false}" class="media">
    <div class="mr-2">
      <a v-profile="comment.user.id">
        <vue-avatar v-bind="comment.user" class="i-35 d-block img-thumbnail"></vue-avatar>
      </a>
    </div>

    <div class="media-body d-flex">
      <vue-comment-form v-if="comment.is_editing" :microblog="comment" ref="form" class="w-100 mr-1" @cancel="edit(comment)" @save="edit(comment)"></vue-comment-form>

      <div v-if="!comment.is_editing" class="w-100">
        <h6><vue-user-name :user="comment.user"></vue-user-name></h6>

        <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"></vue-flag>

        <div class="comment-text" v-html="comment.html"></div>

        <ul class="d-none d-sm-block list-inline list-inline-bullet-sm text-muted small m-0">
          <li class="list-inline-item">
            <a :href="comment.url" class="text-muted">
              <vue-timeago :datetime="comment.created_at"></vue-timeago>
            </a>
          </li>

          <li class="list-inline-item">
            <a @click="checkAuth(vote, comment)" :title="commentVoters" :class="{'text-primary': comment.is_voted, 'text-muted': !comment.is_voted}" href="javascript:">
              {{ commentLabel }}
            </a>
          </li>

          <li class="list-inline-item">
            <a @click="checkAuth(reply)" href="javascript:" class="text-muted">Odpowiedz</a>
          </li>

          <li v-if="isAuthorized" class="list-inline-item">
            <a href="javascript:" :data-metadata="comment.metadata" :data-url="comment.url" class="text-muted">Zgłoś</a>
          </li>
        </ul>

        <ul class="d-sm-none list-inline text-muted small m-0">
          <li class="list-inline-item">
            <a :href="comment.url" class="text-muted">
              <vue-timeago :datetime="comment.created_at"></vue-timeago>
            </a>
          </li>

          <li class="list-inline-item">
            <a href="#" class="text-muted" data-toggle="dropdown">
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

      <div v-if="comment.editable" class="dropdown">
        <button class="btn btn-xs dropdown-toggle border-0" type="button" data-toggle="dropdown" aria-label="Dropdown"></button>

        <div class="dropdown-menu dropdown-menu-right">
          <a @click="edit(comment)" class="dropdown-item btn-sm-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
          <a @click="deleteItem" class="dropdown-item btn-sm-remove" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
        </div>
      </div>
    </div>

    <vue-modal ref="confirm">
      Czy na pewno chcesz usunąć ten komentarz?

      <template slot="buttons">
        <button @click="$refs.confirm.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj
        </button>
        <button @click="deleteItem(false)" type="submit" class="btn btn-danger danger">Tak, usuń</button>
      </template>
    </vue-modal>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import VueAvatar from '../avatar.vue';
  import VueUserName from '../user-name.vue';
  import VueTimeago from '../../plugins/timeago';
  import VueCommentForm from "./comment-form.vue";
  import VueModal from '../modal.vue';
  import VueFlag from '../flags/flag.vue';
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref, Mixins } from "vue-property-decorator";
  import { mapActions, mapGetters, mapMutations } from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import { Microblog } from "../../types/models";
  import { MicroblogMixin } from "../mixins/microblog";
  import declination from '../../libs/declination';

  Vue.use(VueTimeago);

  @Component({
    name: 'comment',
    mixins: [clickaway, mixins],
    store,
    components: {
      'vue-avatar': VueAvatar,
      'vue-modal': VueModal,
      'vue-user-name': VueUserName,
      'vue-comment-form': VueCommentForm,
      'vue-flag': VueFlag
    },
    computed: mapGetters('user', ['isAuthorized']),
    methods: {
      ...mapActions('microblogs', ['vote', 'loadVoters'])
    }
  })
  export default class VueComment extends Mixins(MicroblogMixin) {

    @Ref()
    readonly confirm!: VueModal;

    @Prop(Object)
    comment!: Microblog;

    reply() {
      this.$emit('reply', this.comment.user);
    }

    deleteItem(confirm: boolean) {
      this.delete('microblogs/deleteComment', confirm, this.comment);
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
      return this.comment.voters!.join(', ');
    }

    get flags() {
      return store.getters['flags/filter'](this.comment.id);
    }
  }
</script>

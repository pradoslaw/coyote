<template>
  <div :id="`comment-${comment.id}`" :class="{'highlight-flash': highlight}"  class="media">
    <div class="mr-2">
      <a v-profile="comment.user.id">
        <vue-avatar v-bind="comment.user" class="i-35 d-block img-thumbnail"></vue-avatar>
      </a>
    </div>

    <div class="media-body d-flex">
      <vue-comment-form v-if="isEditing" :microblog="comment" ref="form" class="flex-grow-1 mr-1" @cancel="isEditing = false" @save="isEditing = false"></vue-comment-form>

      <div v-if="!isEditing" class="break-word flex-grow-1">
        <h6><vue-user-name :user="comment.user"></vue-user-name></h6>
        <div class="comment-text" v-html="comment.html"></div>

        <ul class="list-inline list-inline-bullet-sm text-muted small m-0">
          <li class="list-inline-item">
            <a :href="comment.url" class="text-muted">
              <vue-timeago :datetime="comment.created_at"></vue-timeago>
            </a>
          </li>
          <li class="list-inline-item">
            <a @click="vote(comment)" :title="comment.voters.join(', ')" :class="{'thumbs-on': comment.is_voted}" href="javascript:" class="text-muted btn-sm-thumbs">
              {{ comment.votes }} {{ comment.votes | declination(['głos', 'głosy', 'głosów']) }}
            </a>
          </li>
        </ul>
      </div>

      <div v-if="comment.editable" class="dropdown">
        <button class="btn btn-xs dropdown-toggle border-0" type="button" data-toggle="dropdown"></button>

        <div class="dropdown-menu dropdown-menu-right">
          <a @click="edit" class="dropdown-item btn-sm-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
          <a @click="deleteItem" class="dropdown-item btn-sm-remove"><i class="fas fa-times fa-fw"></i> Usuń</a>
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
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref, Mixins } from "vue-property-decorator";
  import {mapActions, mapGetters} from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import { Microblog } from "../../types/models";
  import { MicroblogMixin } from "../mixins/microblog";

  Vue.use(VueTimeago);

  @Component({
    name: 'comment',
    mixins: [clickaway, mixins],
    store,
    components: { 'vue-avatar': VueAvatar, 'vue-modal': VueModal, 'vue-user-name': VueUserName, 'vue-comment-form': VueCommentForm },
    computed: mapGetters('user', ['isAuthorized']),
    methods: mapActions('microblogs', ['vote', 'loadVoters'])
  })
  export default class VueComment extends Mixins(MicroblogMixin) {

    @Ref()
    readonly confirm!: VueModal;

    @Prop(Object)
    comment!: Microblog;

    deleteItem(confirm: boolean) {
      this.delete('microblogs/deleteComment', confirm, this.comment);
    }

    get anchor() {
      return `comment-${this.comment.id}`;
    }

    get highlight() {
      return '#' + this.anchor === window.location.hash;
    }
  }
</script>

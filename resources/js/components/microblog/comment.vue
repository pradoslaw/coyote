<template>
  <div class="media">
    <div class="mr-2">
      <a v-profile="comment.user.id">
        <vue-avatar v-bind="comment.user" class="i-35 d-sm-block img-thumbnail"></vue-avatar>
      </a>
    </div>

    <div class="media-body d-flex">

      <vue-comment-form v-if="isEditing" :microblog="comment" ref="form" class="flex-grow-1 mr-1"></vue-comment-form>

      <div v-if="!isEditing" class="comment-body flex-grow-1">
        <h6><vue-user-name :user="comment.user"></vue-user-name></h6>
        <div class="media-content" v-html="comment.html"></div>

        <ul class="list-inline list-inline-bullet-sm text-muted small m-0">
          <li class="list-inline-item">
            <a :href="`/Mikroblogi/View/${$parent.microblog.id}#entry-${comment.id}`" class="text-muted">
              <vue-timeago :datetime="comment.created_at"></vue-timeago>
            </a>
          </li>
          <li class="list-inline-item">
            <a @click="vote(microblog)" :class="{'thumbs-on': comment.is_voted}" class="text-muted btn-sm-thumbs" data-toggle="tooltip" data-placement="top">
              {{ comment.votes }} {{ comment.votes | declination(['głos', 'głosy', 'głosów']) }}
            </a>
          </li>
        </ul>
      </div>

      <div v-if="comment.editable" class="dropdown">
        <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown"></button>

        <div class="dropdown-menu dropdown-menu-right">
          <a @click="edit" class="dropdown-item btn-sm-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
          <a @click="deleteItem" class="dropdown-item btn-sm-remove"><i class="fas fa-times fa-fw"></i> Usuń</a>
        </div>
      </div>
    </div>
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
  import { Prop, Ref } from "vue-property-decorator";
  import {mapActions, mapGetters, mapState} from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import { Microblog } from "../../types/models";

  Vue.use(VueTimeago);

  @Component({
    name: 'comment',
    mixins: [clickaway, mixins],
    store,
    components: { 'vue-avatar': VueAvatar, 'vue-modal': VueModal, 'vue-user-name': VueUserName, 'vue-comment-form': VueCommentForm },
    computed: mapGetters('user', ['isAuthorized']),
    methods: mapActions('microblogs', ['vote'])
  })
  export default class VueComment extends Vue {
    isEditing = false;

    @Ref()
    readonly confirm!: VueModal;

    @Ref('form')
    readonly form!: VueCommentForm;

    //
    @Prop(Object)
    comment!: Microblog;

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        this.$nextTick(function () {
          // @ts-ignore
          this.form.textarea.focus();
        })
      }
    }

    deleteItem(confirm: number) {
      if (confirm) {
        /* @ts-ignore */
        // this.confirm.open();
      } else {
        // this.confirm.close();

        store.dispatch('microblog/delete')
      }
    }

  }
</script>

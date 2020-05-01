<template>
  <div class="media">
    <div class="mr-2">
      <a v-profile="comment.user.id">
        <vue-avatar v-bind="comment.user" class="i-35 d-sm-block img-thumbnail"></vue-avatar>
      </a>
    </div>

    <div class="media-body">
      <div v-if="comment.editable" class="dropdown float-right">
        <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="comment-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>

        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="comment-menu">
          <a @click="edit" class="dropdown-item btn-sm-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
          <a @click="deleteItem" class="dropdown-item btn-sm-remove"><i class="fas fa-times fa-fw"></i> Usuń</a>
        </div>
      </div>

      <vue-comment-form v-if="isEditing" :microblog="comment"></vue-comment-form>

<!--      {{ form_open({url: route('microblog.comment.save', [comment.id]), class: 'write-content mr-4', style: 'display: none'}) }}-->
<!--      <textarea name="text" placeholder="Napisz komentarz... (Ctrl+Enter aby wysłać)" class="form-control" data-prompt-url="{{ route('user.prompt') }}" rows="1">{{ comment.text }}</textarea>-->
<!--      <button type="submit" class="btn btn-sm btn-submit" title="Zapisz (Ctrl+Enter)"><i class="far fa-fw fa-share-square"></i></button>-->
<!--      {{ form_close() }}-->

      <div v-if="!isEditing" class="comment-body">
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

    //
    @Prop(Object)
    comment!: Microblog;

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        // this.$nextTick(function () {
        //   this.$refs.submitText.$el.focus();
        // })
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

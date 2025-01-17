<template>
  <div class="card">
    <div class="card-body">
      <div class="media">
        <div class="me-2">
          <a v-profile="user.id">
            <vue-avatar
              :name="user.name" 
              :photo="user.photo" 
              :initials="user.initials"
              :id="user.id"
              :is-online="user.is_online"
              class="img-thumbnail media-object i-38"/>
          </a>
        </div>

        <div class="media-body">
          <vue-markdown
            v-model="defaultText"
            :emojis="emojis"
            @save="saveComment"
            ref="markdown"
            preview-url="/Mikroblogi/Preview"/>

          <div class="d-flex mt-2 justify-content-end">
            <vue-button :disabled="isSubmitting" @click="saveComment" class="btn btn-primary btn-sm" tabindex="3" title="Ctrl+Enter aby opublikować">
              Zapisz
            </vue-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {mapState} from 'vuex';
import store from "../../store/index";

import {Emojis} from '../../types/models';
import {nextTick} from "../../vue";
import VueAvatar from '../avatar.vue';
import VueButton from '../forms/button.vue';
import VueMarkdown from '../forms/markdown.vue';
import mixins from '../mixins/user.js';

export default {
  name: 'VueForm',
  components: {
    'vue-avatar': VueAvatar,
    'vue-button': VueButton,
    'vue-markdown': VueMarkdown,
  },
  mixins: [mixins],
  computed: {
    ...mapState('user', ['user']),
  },
  props: {
    resource: {required: true},
    resourceId: {required: true},
  },
  data() {
    return {
      emojis: {} as Emojis,
      isSubmitting: false,
      defaultText: '',
    };
  },
  created() {
    this.emojis = window.emojis;
  },
  methods: {
    saveComment() {
      this.isSubmitting = true;
      store.dispatch('comments/save', {text: this.defaultText, resource_type: this.resource, resource_id: this.resourceId})
        .then(response => {
          this.defaultText = '';
          this.scrollIntoView(response.data);
        })
        .finally(() => {
          this.isSubmitting = false;
        });
    },
    scrollIntoView(comment) {
      nextTick(() => {
        window.location.hash = `comment-${comment.id}`;
      });
    },
  },
};
</script>

<template>
  <div class="card card-default">
    <div class="card-body">
      <div class="media">
        <div class="mr-2">
          <a v-profile="user.id">
            <vue-avatar :name="user.name" :photo="user.photo" :id="user.id" :is-online="user.is_online" class="img-thumbnail media-object i-38"></vue-avatar>
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
            <vue-button :disabled="isSubmitting" @click.native="saveComment" class="btn btn-primary btn-sm" tabindex="3" title="Ctrl+Enter aby opublikować">Zapisz</vue-button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import {Emojis, Model} from '@/types/models';
import Vue from 'vue';
import Component from "vue-class-component";
import {Prop} from "vue-property-decorator";
import {mapState} from 'vuex';
import VueAvatar from '../avatar.vue';
import VueButton from '../forms/button.vue';
import VueMarkdown from '../forms/markdown.vue';
import VuePromp from '../forms/prompt.vue';
import {default as mixins} from '../mixins/user';

// @ts-ignore
@Component({
  components: {
    'vue-avatar': VueAvatar,
    'vue-button': VueButton,
    'vue-prompt': VuePromp,
    'vue-markdown': VueMarkdown
  },
  mixins: [mixins],
  computed: mapState('user', ['user'])
})
export default class VueForm extends Vue {
  public emojis!: Emojis;

  @Prop()
  readonly resource!: Model;

  @Prop()
  readonly resourceId!: number;

  isSubmitting = false;
  defaultText = '';

  created() {
    this.emojis = window.emojis;
  }

  saveComment() {
    this.isSubmitting = true;

    this.$store.dispatch('comments/save', {text: this.defaultText, resource_type: this.resource, resource_id: this.resourceId})
      .then(response => {
        this.defaultText = '';
        this.scrollIntoView(response.data);
      })
      .finally(() => this.isSubmitting = false);
  }

  scrollIntoView(comment) {
    this.$nextTick(() => window.location.hash = `comment-${comment.id}`);
  }
}
</script>

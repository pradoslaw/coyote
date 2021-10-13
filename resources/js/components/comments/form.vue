<template>
  <div class="card card-default">
    <div class="card-body">
      <div class="comment">
        <div class="media">
          <div class="mr-2">
            <a v-profile="user.id">
              <vue-avatar v-bind="user" :is-online="user.is_online" class="img-thumbnail media-object"></vue-avatar>
            </a>
          </div>

          <div class="media-body">
            <div class="form-group">
              <vue-prompt>
                <textarea
                  v-autosize
                  placeholder="Zadaj pytanie"
                  name="text"
                  class="form-control"
                  ref="textarea"
                  v-model="defaultText"
                  @keydown.ctrl.enter="saveComment"
                  rows="1"
                  tabindex="1"
                ></textarea>
              </vue-prompt>
            </div>

            <div class="row">
              <div class="col-12">
                <vue-button :disabled="isSubmitting" @click.native="saveComment" class="btn btn-primary btn-sm float-right" tabindex="3" title="Ctrl+Enter aby opublikować">Zapisz</vue-button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { Prop } from "vue-property-decorator";
  import Component from "vue-class-component";
  import VueButton from '../forms/button.vue';
  import VueAvatar from '../avatar.vue';
  import { Model } from '@/types/models';
  import VuePromp from '../forms/prompt.vue';
  import { mapState } from 'vuex';
  import { default as mixins } from '../mixins/user';

  @Component({
    components: {
      'vue-avatar': VueAvatar,
      'vue-button': VueButton,
      'vue-prompt': VuePromp
    },
    mixins: [ mixins ],
    computed: mapState('user', ['user'])
  })
  export default class VueForm extends Vue {
    @Prop()
    readonly resource!: Model;

    @Prop()
    readonly resourceId!: number;

    isSubmitting = false;
    defaultText = '';

    saveComment() {
      this.isSubmitting = true;

      this.$store.dispatch('comments/save', { text: this.defaultText, resource_type: this.resource, resource_id: this.resourceId })
        // .then(response => {
        //   this.isEditing = false;
        //   this.isReplying = false;
        // })
        .finally(() => this.isSubmitting = false);
    }


  }
</script>

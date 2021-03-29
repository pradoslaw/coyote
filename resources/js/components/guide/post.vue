<template>
  <div class="card card-default">
    <vue-form v-if="guide.is_editing" class="card-body"></vue-form>

    <div v-if="!guide.is_editing" class="card-body">
      <div class="guide-title">
        <div v-if="guide.permissions.update" class="dropdown float-right">
          <button class="btn btn-xs border-0 text-muted mt-2" type="button" data-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis-h"></i></button>

          <div class="dropdown-menu dropdown-menu-right">
            <a @click="edit" class="dropdown-item" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
            <a @click="deleteItem" class="dropdown-item" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
          </div>
        </div>

        <h1><a :href="`/Guide/${guide.id}-${guide.slug}`">{{ guide.title }}</a></h1>
      </div>

      <div v-html="guide.excerpt_html" class="mt-2"></div>

      <div class="guide-text">
        <div v-html="guide.html" :class="{'blur': !isShowing}"></div>

        <button v-if="!isShowing" @click="isShowing = true" class="btn btn-primary">Zobacz odpowiedź</button>
      </div>

      <vue-tags :tags="guide.tags" class="tag-clouds-skills mt-3"></vue-tags>

      <div class="mt-3 pt-3 qa-options">
        <ul class="list-inline mb-2">
          <li class="list-inline-item">
            <a href="#">
              <i class="fa text-primary fa-fw fa-thumbs-up"></i>
              {{ guide.votes }} {{ guide.votes | declination(['głos', 'głosy', 'głosów']) }}
            </a>
          </li>

          <li class="list-inline-item">
            <a href="#">
              <i class="far fa-fw fa-star"></i>

              10 obserwatorów
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import VueForm from './form.vue';
  import { Prop } from "vue-property-decorator";
  import { Guide } from '@/types/models';
  import { default as mixins } from '../mixins/user';
  import VueTags from "@/components/tags.vue";
  import { mapState } from 'vuex';

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-tags': VueTags,
      'vue-form': VueForm
    },
    computed: {
      ...mapState('guides', ['guide'])
    }
  })
  export default class VuePost extends Vue {
    private isShowing = false;

    edit() {
      this.$store.commit('guides/edit');
    }

    deleteItem() { }

  }
</script>

<template>
  <div class="card card-default">
    <vue-form v-if="guide.is_editing" class="card-body"></vue-form>

    <div v-if="!guide.is_editing" class="card-body">

      <div v-if="guide.permissions.update" class="dropdown float-right">
        <button class="btn btn-xs border-0 text-muted mt-2" type="button" data-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis-h"></i></button>

        <div class="dropdown-menu dropdown-menu-right">
          <a @click="edit" class="dropdown-item" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
          <a @click="deleteItem" class="dropdown-item" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
        </div>
      </div>

      <h1 class="m-0"><a :href="`/Guide/${guide.id}-${guide.slug}`">{{ guide.title }}</a></h1>

      <ul class="metadata list-inline">
        <li class="list-inline-item">
          <i class="fa fa-user-alt"></i> <vue-user-name :user="guide.user" />
        </li>

        <li class="list-inline-item">
          <i class="far fa-calendar-alt"></i> <vue-timeago :datetime="guide.created_at"></vue-timeago>
        </li>

        <li class="list-inline-item">
          <i class="far fa-comments"></i> {{ guide.comments_count }} {{ guide.comments_count | declination(['komentarz', 'komentarze', 'komentarzy']) }}
        </li>

        <li class="list-inline-item">
          <i class="far fa-eye"></i>

          {{ guide.views }} {{ guide.views | declination(['wyświetlenie', 'wyświetlenia', 'wyświetleń']) }}
        </li>
      </ul>

      <div class="row no-gutters">
        <vue-tags :tags="guide.tags" class="tag-clouds-skills mt-2 mb-2"></vue-tags>

        <div class="ml-auto text-right">
          <p class="text-muted font-weight-bold mb-1"><i class="fas fa-fw fa-chart-line"></i> Mid-level</p>


          <i class="fas fa-circle text-primary" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i><i class="fas fa-circle text-primary" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i><i class="fas fa-circle text-muted" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i>
<!--          <div class="btn-group btn-group-sm" role="group">-->
<!--            <button type="button" class="btn btn-primary">Junior</button>-->
<!--            <button type="button" class="btn btn-warning">Mid-level</button>-->
<!--            <button type="button" class="btn btn-danger">Senior</button>-->
<!--          </div>-->
        </div>

      </div>

      <div v-html="guide.excerpt_html" class="mt-3"></div>

      <div class="guide-text">
        <div v-html="guide.html" :class="{'blur': !isShowing}"></div>

        <button v-if="!isShowing" @click="isShowing = true" class="btn btn-primary">Zobacz odpowiedź</button>
      </div>

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
  import VueUserName from '@/components/user-name';
  import { Prop } from "vue-property-decorator";
  import { Guide } from '@/types/models';
  import { default as mixins } from '../mixins/user';
  import VueTags from "@/components/tags.vue";
  import { mapState } from 'vuex';

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-tags': VueTags,
      'vue-form': VueForm,
      'vue-user-name': VueUserName
    },
    computed: {
      ...mapState('guides', ['guide'])
    }
  })
  export default class VuePost extends Vue {
    private isShowing = false;

    edit() {
      this.$store.commit('guides/EDIT');
    }

    deleteItem() { }

  }
</script>

<template>
  <div class="card card-default">
    <vue-form v-if="guide.is_editing" class="card-body"></vue-form>

    <div v-if="!guide.is_editing" class="card-body">
      <div v-if="guide.permissions.update" class="dropdown float-right">
        <button class="btn btn-xs border-0 text-muted mt-2" type="button" data-bs-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis"></i></button>

        <div class="dropdown-menu dropdown-menu-right">
          <a @click="edit" class="dropdown-item" href="javascript:"><i class="fas fa-pen-to-square fa-fw"></i> Edytuj</a>
          <a @click="deleteItem" class="dropdown-item" href="javascript:"><i class="fas fa-trash-can fa-fw"></i> Usuń</a>
        </div>
      </div>

      <h1 class="m-0"><a :href="guide.url">{{ guide.title }}</a></h1>

      <vue-metadata :guide="guide"></vue-metadata>

      <div class="row no-gutters">
        <vue-tags :tags="guide.tags" class="mt-2 mb-2"></vue-tags>

        <div class="ml-auto text-right">
          <p class="text-muted font-weight-bold mb-1"><i class="fas fa-fw fa-chart-line"></i> {{ seniorityLabel }}</p>

          <vue-progress-bar
            v-model="progressBarValue"
            :editable="true"
            :tooltips="Object.values(roles)"
            @click="setRole"
            data-popover='{"message": "Możesz zmienić typ tej roli jeżeli się z nią nie zgadzasz.", "placement": "left", "offset": [0, 20]}'
          />
        </div>
      </div>

      <div v-html="guide.excerpt_html" class="mt-3"></div>

      <div class="guide-text">
        <div v-html="guide.html" :class="{'blur': !isShowing}"></div>

        <button v-if="!isShowing" @click="isShowing = true" class="btn btn-primary">Zobacz odpowiedź</button>
      </div>

      <div class="mt-3 pt-3 border-top">
        <ul class="list-inline mb-2">
          <li class="list-inline-item">
            <a @click="checkAuth(vote, guide)" href="javascript:" title="Kliknij jeżeli uważasz ten wpis za wartościowy" class="btn btn-gradient">
              <i :class="{'fa text-primary': guide.is_voted, 'far': !guide.is_voted}" class="fa-fw fa-thumbs-up"></i>

              {{ guide.votes }} {{ guide.votes | declination(['głos', 'głosy', 'głosów']) }}
            </a>
          </li>

          <li class="list-inline-item">
            <a @click="checkAuth(subscribe, guide)" href="javascript:" title="Otrzymuj powiadomienia o zmianach na tej stronie" class="btn btn-gradient">
              <i :class="{'fa text-primary': guide.is_subscribed, 'far': !guide.is_subscribed}" class="fa-fw fa-bell"></i>

              {{ guide.subscribers }} {{ guide.subscribers | declination(['obserwator', 'obserwatorów', 'obserwatorów']) }}
            </a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
  import Component from "vue-class-component";
  import VueForm from './form.vue';
  import VueMetadata from './metadata.vue';
  import VueProgressBar from "@/components/progress-bar.vue";
  import { default as mixins } from '../mixins/user';
  import VueTags from "@/components/tags.vue";
  import { mapActions, mapGetters, mapState } from 'vuex';
  import { Mixins } from "vue-property-decorator";
  import { GuideMixin } from '@/components/mixins/guide';

  @Component({
    mixins: [ mixins ],
    components: {
      'vue-tags': VueTags,
      'vue-form': VueForm,
      'vue-metadata': VueMetadata,
      'vue-progress-bar': VueProgressBar
    },
    computed: {
      ...mapGetters('user', ['isAuthorized']),
      ...mapState('guides', ['guide'])
    },
    methods: {
      ...mapActions('guides', ['vote', 'subscribe'])
    }
  })
  export default class VuePost extends Mixins(GuideMixin) {
    private isShowing = false;

    edit() {
      this.$store.commit('guides/EDIT');
    }

    setRole(value: number) {
      const role = Object.keys(this.roles)[value - 1]

      this.$store.dispatch('guides/setRole', { guide: this.guide, role });
    }

    deleteItem() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć wpis?',
        okLabel: 'Tak, usuń'
      })
      .then(() => this.$store.dispatch('guides/delete', this.guide))
      .then(() => window.location.href = '/Guide');
    }
  }
</script>

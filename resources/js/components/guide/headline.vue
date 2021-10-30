<template>
  <div class="card card-default">
    <div class="card-body">
      <h4 class="m-0"><a :href="guide.url">{{ guide.title }}</a></h4>

      <vue-metadata :guide="guide"></vue-metadata>

      <div class="row no-gutters">
        <vue-tags :tags="guide.tags" class="tag-clouds-skills mt-2 mb-2"></vue-tags>

        <div class="ml-auto text-right">
          <p class="text-muted font-weight-bold mb-1"><i class="fas fa-fw fa-chart-line"></i> Mid-level</p>

          <i class="fas fa-circle text-primary" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i><i class="fas fa-circle text-primary" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i><i class="fas fa-circle text-muted" title="zaawansowany" style="font-size: 10px; margin-right: 4px;"></i>
        </div>
      </div>

      <ul class="list-inline text-muted small mt-1 mb-0">
        <li class="list-inline-item">
          <a @click="checkAuth(vote, guide)" class="text-muted" href="javascript:" title="Kliknij jeżeli uważasz ten wpis za wartościowy">
            <i :class="{'fa text-primary': guide.is_voted, 'far': !guide.is_voted}" class="fa-fw fa-thumbs-up"></i>

            {{ guide.votes }} {{ guide.votes | declination(['głos', 'głosy', 'głosów']) }}
          </a>
        </li>

        <li class="list-inline-item">
          <a @click="checkAuth(subscribe, guide)" class="text-muted" href="javascript:" title="Otrzymuj powiadomienia o zmianach na tej stronie">
            <i :class="{'fa text-primary': guide.is_subscribed, 'far': !guide.is_subscribed}" class="fa-fw fa-bell"></i>

            {{ guide.subscribers }} {{ guide.subscribers | declination(['obserwator', 'obserwatorów', 'obserwatorów']) }}
          </a>
        </li>
      </ul>
    </div>
  </div>
</template>

<script lang="ts">
  import Vue from "vue";
  import Component from "vue-class-component";
  import VueTags from "@/components/tags.vue";
  import VueUserName from "@/components/user-name.vue";
  import VueMetadata from './metadata.vue';
  import {mapActions, mapGetters, mapState} from "vuex";
  import {default as mixins} from '../mixins/user';
  import {Prop} from "vue-property-decorator";
  import {Guide} from '@/types/models';

  @Component({
    mixins: [mixins],
    components: {
      'vue-tags': VueTags,
      'vue-user-name': VueUserName,
      'vue-metadata': VueMetadata
    },
    methods: {
      ...mapGetters('user', ['isAuthorized']),
      ...mapActions('guides', ['vote', 'subscribe'])
    }
  })
  export default class VueHeadline extends Vue {
    @Prop()
    readonly guide!: Guide;
  }
</script>

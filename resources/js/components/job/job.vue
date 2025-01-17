<template>
  <div :class="{'highlight': job.is_highlight}" class="card card-job mb-3">
    <span v-if="order === 0 && job.is_on_top" class="label top-spot-badge d-none d-sm-block">
      Promowane
    </span>
    <div class="card-body">
      <div class="media">
        <div class="d-none d-sm-block me-3">
          <a :href="job.url">
            <img
              :src="job.firm.logo || '/img/logo-gray.png'"
              :alt="job.firm.logo ? job.firm.name : ''"
              class="i-95">
          </a>
        </div>
        <div class="media-body">
          <h4 class="float-start">
            <a :href="job.url">{{ job.title }}</a>
          </h4>
          <a v-if="job.is_medal"
             :href="job.url"
             class="medal d-none d-sm-inline-block"
             title="Oferta na medal. To odznaczenie przyznawane jest ofertom, które zawierają szczegółowe informacje o pracy"
          />
          <vue-salary
            :salary_from="job.salary_from"
            :salary_to="job.salary_to"
            :currency_symbol="job.currency_symbol"
            :rate="job.rate"
            :is_gross="job.is_gross"
            :options="{'class': 'float-end'}"/>
          <p class="location pb-1 mb-2" style="clear: left">
            <a class="employer" :title="'Zobacz oferty pracy z ' + job.firm.name" :href="job.firm.url">
              {{ job.firm.name }}
            </a>
            <vue-location :locations="job.locations" :remote="job.remote"></vue-location>
          </p>
          <div class="clearfix"/>
          <span v-if="job.is_new" class="badge label-new float-end mt-2">Nowe</span>
          <small v-else class="text-muted float-end">{{ job.boost_at }}</small>
          <ul class="tag-clouds tag-clouds-sm">
            <li v-for="tag in limitedTags">
              <a :href="tag.url" :title="'Znajdź oferty zawierające ' + tag.name">
                <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">
                {{ tag.real_name || tag.name }}
              </a>
            </li>
          </ul>
          <ul class="list-inline job-options mt-2">
            <li class="list-inline-item">
              <a @click="checkAuth(subscribe)" href="javascript:">
                <span v-if="isSubscribed(job)" class="on">
                  <vue-icon name="jobOfferSubscribed"/>
                </span>
                <vue-icon v-else name="jobOfferSubscribe"/>
                Ulubiona
              </a>
            </li>
            <li class="list-inline-item">
              <a :href="job.url + '#comments'">
                <vue-icon name="jobOfferComments"/>
                {{ job.comments_count }}
                {{ declination(job.comments_count, ['komentarz', 'komentarze', 'komentarzy']) }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapGetters} from 'vuex';
import store from '../../store/index';
import VueIcon from '../icon';
import {default as mixins} from '../mixins/user';
import VueLocation from './location.vue';
import VueSalary from './salary.vue';

export default {
  props: {
    job: {
      type: Object,
      required: true,
    },
    order: {
      type: Number,
    },
  },
  mixins: [mixins],
  components: {
    VueIcon,
    'vue-salary': VueSalary,
    'vue-location': VueLocation,
  },
  methods: {
    subscribe() {
      store.dispatch('jobs/subscribe', this.job);
    },
  },
  computed: {
    limitedTags: function () {
      return this.job.tags.slice(0, 5);
    },
    ...mapGetters('jobs', ['isSubscribed']),
    ...mapGetters('user', ['isAuthorized']),
  },
};
</script>

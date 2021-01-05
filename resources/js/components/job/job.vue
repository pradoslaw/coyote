<template>
  <div :class="{'highlight': job.is_highlight}" class="card card-default card-job mb-3">
    <span v-if="order === 0 && job.is_on_top" class="label top-spot-badge d-none d-sm-block">Promowane</span>

    <div class="card-body">
      <div class="media">
        <div class="d-none d-sm-block mr-3">
          <a :href="job.url">
            <img :src="job.firm.logo || '/img/logo-gray.png'" :alt="job.firm.logo ? job.firm.name : ''" class="i-95">
          </a>
        </div>

        <div class="media-body">
          <h4 class="float-left"><a :href="job.url">{{ job.title }}</a></h4>

          <a v-if="job.is_medal" :href="job.url" class="medal d-none d-sm-inline-block" title="Oferta na medal. To odznaczenie przyznawane jest ofertom, które zawierają szczegółowe informacje o pracy"></a>

          <vue-salary
            :salary_from="job.salary_from"
            :salary_to="job.salary_to"
            :currency_symbol="job.currency_symbol"
            :rate="job.rate"
            :is_gross="job.is_gross"
            :options="{'class': 'float-right'}"
          >
          </vue-salary>

          <p class="pb-1 border-bottom" style="clear: left">
            <a class="employer" :title="'Zobacz oferty pracy z ' + job.firm.name" :href="job.firm.url">{{ job.firm.name }}</a>

            <vue-location :locations="job.locations" :remote="job.remote"></vue-location>
          </p>

          <span v-if="job.is_new" class="badge label-new float-right mt-2">Nowe</span>
          <small v-else class="text-muted float-right">{{ job.boost_at }}</small>

          <ul class="tag-clouds tag-clouds-sm tag-clouds-skills">
            <li v-for="tag in limitedTags">
              <a :href="tag.url" :title="'Znajdź oferty zawierające ' + tag.name">
                <img v-if="tag.logo" :alt="tag.name" :src="tag.logo">

                {{ tag.real_name || tag.name }}
              </a>
            </li>
          </ul>

          <ul class="list-inline job-options mt-2">
            <li class="list-inline-item">
              <a @click="checkAuth(subscribe)" href="javascript:"><i :class="{'fas fa-heart on': isSubscribed(job), 'far fa-heart': !isSubscribed(job)}" class="fa-fw"></i>
                Ulubiona</a>
            </li>
            <li class="list-inline-item">
              <a :href="job.url + '#comments'"><i class="far fa-fw fa-comment"></i> {{ job.comments_count }} {{ job.comments_count | declination(['komentarz', 'komentarze', 'komentarzy']) }}</a>
            </li>
            <!--<li><a href="#"><i class="fa fa-fw fa-share"></i> Udostępnij</a></li>-->
          </ul>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import VueSalary from './salary.vue';
  import VueLocation from './location.vue';
  import { mapGetters } from 'vuex';
  import { default as mixins } from '../mixins/user';

  export default {
    props: {
      job: {
        type: Object,
        required: true
      },
      order: {
        type: Number
      }
    },
    mixins: [ mixins ],
    components: {
      'vue-salary': VueSalary,
      'vue-location': VueLocation
    },
    methods: {
      subscribe() {
        this.$store.dispatch('jobs/subscribe', this.job);
      }
    },
    computed: {
      limitedTags: function () {
        return this.job.tags.slice(0, 5);
      },

      ...mapGetters('jobs', ['isSubscribed']),
      ...mapGetters('user', ['isAuthorized'])
    }
  }
</script>

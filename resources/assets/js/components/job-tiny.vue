<template>
  <div class="media text-truncate">
    <a class="media-link" :href="job.url" :title="fullTitle">
      <div class="media-object d-none d-xl-inline-block mr-2">
        <object :data="job.firm.logo || '//'" type="image/png" class="media-object d-inline-block">
          <img src="/img/logo-gray.png" :alt="job.firm.logo ? job.firm.name : ''">
        </object>
      </div>

      <div class="media-body">
        <h5 class="mb-1">{{ job.title }}</h5>

        <p>
          <span class="employer">{{ job.firm.name }}</span>

          <vue-location :locations="job.locations" :remote="job.remote" :clickable="false" :shortened="true"></vue-location>
        </p>

        <p>
          <vue-salary
            :salary_from="job.salary_from"
            :salary_to="job.salary_to"
            :currency_symbol="job.currency_symbol"
            :rate="job.rate"
            :isGross="job.is_gross"
          >
          </vue-salary>
        </p>

        <ul class="tag-clouds tag-clouds-xs">
          <li v-for="tag in limitedTags">
            <span>
              {{ tag.name }}
            </span>
          </li>
        </ul>
      </div>
    </a>
  </div>
</template>

<script>
  import VueSalary from './salary.vue';
  import VueLocation from './location.vue';

  export default {
    props: ['job'],
    components: {
      'vue-salary': VueSalary,
      'vue-location': VueLocation
    },
    computed: {
      limitedTags() {
        return this.job.tags.slice(0, 5);
      },

      fullTitle() {
        return [this.job.title, this.job.firm.name || ''].join(' @ ');
      }
    }
  }
</script>

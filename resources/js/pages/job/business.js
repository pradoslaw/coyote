import Vue from 'vue';
import VuePricing from '@/components/job/pricing.vue';

new Vue({
  el: '#js-business',
  delimiters: ['${', '}'],
  components: {
    'vue-pricing': VuePricing
  },
  data: {
    plans,
    plan
  },
  computed: {
    submitUrl() {
      return `/Praca/Submit?default_plan=${this.plan.id}`;
    }
  }
});

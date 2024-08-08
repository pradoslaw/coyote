import VuePricing from '@/components/job/pricing.vue';
import Vue from 'vue';

new Vue({
  name: 'Business',
  el: '#js-business',
  delimiters: ['${', '}'],
  components: {
    'vue-pricing': VuePricing,
  },
  data: {
    plans,
    plan,
  },
  computed: {
    submitUrl() {
      return `/Praca/Submit?default_plan=${this.plan.id}`;
    },
  },
});

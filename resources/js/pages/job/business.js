import VuePricing from '../../components/job/pricing.vue';
import {createVueApp} from '../../vue';

createVueApp('Business', '#js-business', {
  delimiters: ['${', '}'],
  components: {
    'vue-pricing': VuePricing,
  },
  data: () => ({
    plans,
    plan,
  }),
  computed: {
    submitUrl() {
      return `/Praca/Submit?default_plan=${this.plan.id}`;
    },
  },
});

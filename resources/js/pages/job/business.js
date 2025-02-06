import {postJobBoardMilestone} from '../../../feature/jobBoard/jobBoard';
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
  mounted() {
    postJobBoardMilestone('see-landing');
  },
  computed: {
    submitUrl() {
      return `/Praca/Submit?default_plan=${this.plan.id}`;
    },
  },
});

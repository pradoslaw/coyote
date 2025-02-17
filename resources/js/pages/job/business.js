import {postJobBoardMilestone} from '../../../feature/jobBoard/jobBoard';
import VuePricing from '../../components/job/pricing.vue';
import {createVueApp} from '../../vue';

createVueApp('Business', '#js-business', {
  delimiters: ['${', '}'],
  components: {
    'vue-pricing': VuePricing,
  },
  data() {
    return {
      plans,
    };
  },
  mounted() {
    postJobBoardMilestone('see-landing');
  },
  methods: {
    submitUrl(planId) {
      window.location.href = `/Praca/Submit?default_plan=${planId}`;
    },
  },
});

import {postJobBoardMilestone} from '../../../feature/jobBoard/jobBoard';
import VueButton from '../../components/forms/button.vue';
import VueFirmForm from '../../components/job/firm-form.vue';
import VueJobForm from '../../components/job/form.vue';
import VuePricing from '../../components/job/pricing.vue';
import VueTabs from '../../components/tabs.vue';
import store from '../../store/index';
import {createVueAppNotifications} from '../../vue';

createVueAppNotifications('Job submit', '#js-submit-form', {
  store,
  delimiters: ['${', '}'],
  data: () => ({
    plans,
    currencies,
    job,
    defaultBenefits,
    employees,
    firms,
    errors: {},
    isSubmitting: false,
    currentTab: 0,
    tabs: ['Oferta pracy', 'Informacje o firmie'],
  }),
  components: {
    'vue-job-form': VueJobForm,
    'vue-firm-form': VueFirmForm,
    'vue-button': VueButton,
    'vue-pricing': VuePricing,
    'vue-tabs': VueTabs,
  },
  created() {
    store.commit('jobs/INIT_FORM', window.job);
  },
  mounted() {
    document.querySelector('[v-loader]')?.remove();
  },
  methods: {
    switchTab(tab, mode) {
      const tabs = ['offer', 'firm'];
      postJobBoardMilestone('change-tabs-' + tabs[tab] + (mode ? '-' + mode : ''));
      this.currentTab = tab;
      window.scrollTo(0, document.getElementById('js-tabs').offsetTop);
    },

    submitForm() {
      postJobBoardMilestone('save-offer-attempt');
      this.isSubmitting = true;
      this.errors = {};

      store.dispatch('jobs/save')
        .then(function (result) {
          postJobBoardMilestone('save-offer-success');
          return window.location.href = result.data;
        })
        .catch(err => {
          postJobBoardMilestone('save-offer-failure');
          if (err.response.status !== 422) {
            return;
          }

          this.errors = err.response.data.errors;
        })
        .finally(() => this.isSubmitting = false);
    },
  },
});

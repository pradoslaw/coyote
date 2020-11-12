import Vue from 'vue';
import VueNotifications from "vue-notification";
import VuePricing from '@/js/components/job/pricing.vue';
import VueJobForm from '@/js/components/job/form.vue';
import VueFirmForm from '@/js/components/job/firm-form.vue';
import VueButton from '@/js/components/forms/button.vue';
import VueTabs from '@/js/components/tabs.vue';
import store from '@/js/store';
import { default as axiosErrorHandler } from '@/js/libs/axios-error-handler';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  el: '#js-submit-form',
  store,
  delimiters: ['${', '}'],
  data: {
    plans,
    currencies,
    job,
    popularTags,
    defaultBenefits,
    employees,
    firms,
    errors: {},
    isSubmitting: false,
    currentTab: 0,
    tabs: ['Oferta pracy', 'Informacje o firmie']
  },
  components: {
    'vue-job-form': VueJobForm,
    'vue-firm-form': VueFirmForm,
    'vue-button': VueButton,
    'vue-pricing': VuePricing,
    'vue-tabs': VueTabs
  },
  created() {
    store.commit('jobs/INIT_FORM', window.job);
  },
  mounted() {
    document.querySelector('[v-loader]')?.remove();
  },
  methods: {
    switchTab(tab) {
      this.currentTab = tab;
    },

    submitForm() {
      this.isSubmitting = true;
      this.errors = {};

      store.dispatch('jobs/save')
        .then(result => {
          window.location.href = result.data;
        })
        .catch(err => {
          if (err.response.status !== 422) {
            return;
          }

          this.errors = err.response.data.errors;
        })
        .finally(() => this.isSubmitting = false);
    }
  }
});

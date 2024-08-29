import VueForm from '@/components/job/application-form.vue';
import Vue from 'vue';

new Vue({
  name: 'Job',
  el: '#js-application',
  delimiters: ['${', '}'],
  components: {
    'vue-form': VueForm,
  },
  data: () => ({
    application: window.application,
    job: window.job,
  }),
});

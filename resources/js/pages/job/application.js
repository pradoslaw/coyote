import VueForm from '../../components/job/application-form.vue';
import {createVueApp} from '../../vue';

createVueApp('Job', '#js-application', {
  delimiters: ['${', '}'],
  components: {
    'vue-form': VueForm,
  },
  data: () => ({
    application: window.application,
    job: window.job,
  }),
});

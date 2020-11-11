// import tinymce from '../../libs/tinymce';

import Vue from 'vue';

import VuePricing from '../../components/job/pricing.vue';
import VueJobForm from '../../components/job/form.vue';
import VueFirmForm from '../../components/job/firm-form.vue';
// import VueTagsDropdown from '../../components/job/tags-dropdown.vue';
// import VueTagsSkill from '../../components/job/tag-skill.vue';
// import VueGooglePlace from '../../components/google-maps/place.vue';
// import VueText from '../../components/forms/text.vue';
// import VueSelect from '../../components/forms/select.vue';
// import VueCheckbox from '../../components/forms/checkbox.vue';
// import VueRadio from '../../components/forms/radio.vue';
// import VueError from '../../components/forms/error.vue';
import VueButton from '../../components/forms/button.vue';
// import VueModal from '../../components/modal.vue';

// import Editor from '@tinymce/tinymce-vue';
// import axios from "axios";
import store from '../../store';
import { default as axiosErrorHandler } from '../../libs/axios-error-handler';
import VueNotifications from "vue-notification";

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
    errors: {},
    isSubmitting: false
  },
  components: {
    'vue-job-form': VueJobForm,
    'vue-firm-form': VueFirmForm,
    'vue-button': VueButton,
    'vue-pricing': VuePricing
  },
  created() {
    store.commit('jobs/INIT_FORM', window.job);
  },
  mounted() {
    document.querySelector('[v-loader]')?.remove();
  },
  methods: {
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

// new Vue({
//   el: '.submit-form',
//   delimiters: ['${', '}'],
//   data: Object.assign(window.data, {isSubmitting: false, isDone: 0, showFormNavbar: false}),
//   components: {
//     'vue-tinymce': Editor,

//     'vue-pricing': VuePricing,
//     'vue-tags-dropdown': VueTagsDropdown,
//     'vue-tag-skill': VueTagsSkill,
//     'vue-google-place': VueGooglePlace,
//     'vue-text': VueText,
//     'vue-select': VueSelect,
//     'vue-checkbox': VueCheckbox,
//     'vue-radio': VueRadio,
//     'vue-error': VueError,
//     'vue-modal': VueModal,
//     'vue-button': VueButton,

//   },
//
//   mounted() {
//     this.marker = null;
//
//     window.addEventListener('scroll', this.handleScroll);
//     document.querySelector('[v-loader]')?.remove();
//   },
//   destroyed() {
//     window.removeEventListener('scroll', this.handleScroll);
//   },

//


//

//

//
//     addFirm() {
//       this.$refs['add-firm-modal'].open();
//     },
//
//     selectFirm(firmId) {
//       let index = this.firms.findIndex(element => element.id === firmId);
//
//       this.firm = this.firms[index];
//       this.firm.is_private = false;
//
//       // text can not be NULL
//       // tinymce.get('description').setContent(this.firm.description === null ? '' : this.firm.description);
//       this.firm.description = this.firm.description === null ? '' : this.firm.description;
//     },
//
//     _newFirm() {
//       this.$refs['add-firm-modal'].close();
//
//       this.firm = Object.assign(this.firm, {
//         id: null,
//         name: '',
//         logo: {filename: null, url: null},
//         thumbnail: null,
//         description: '',
//         website: null,
//         is_private: false,
//         is_agency: false,
//         employees: null,
//         founded: null,
//         vat_id: null,
//         youtube_url: null,
//         gallery: [{file: ''}],
//         benefits: [],
//         latitude: null,
//         longitude: null,
//         country: null,
//         street: null,
//         postcode: null,
//         city: null,
//         street_number: null,
//         country_id: null
//       });
//     },
//

//

//


//

//
//     handleScroll() {
//       const offset = document.getElementById('footer-top').offsetTop;
//
//       this.showFormNavbar = ((window.scrollY + window.innerHeight) < offset);
//     }
//   },
//   computed: {


//
//     tinymceOptions() {
//       return tinymce;
//     },
//
//     // @todo refaktoring?
//     isPrivate: {
//       get() {
//         return +this.firm.is_private;
//       },
//       set(val) {
//         this.firm.is_private = val;
//       }
//     },
//
//     isAgency: {
//       get() {
//         return +this.firm.is_agency;
//       },
//       set(val) {
//         this.firm.is_agency = val;
//       }
//     },
//

//
//   },
//   watch: {
//     'firm.is_private'(flag) {
//       if (!Boolean(parseInt(flag))) {
//         google.maps.event.trigger(map, 'resize');
//       }
//     },
//
//     'firm.is_agency'() {
//       this.showFormNavbar = false;
//     }
//   }
// });

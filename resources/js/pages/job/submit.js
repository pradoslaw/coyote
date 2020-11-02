import tinymce from '../../libs/tinymce';
import Geocoder from '../../libs/geocoder';
import Vue from 'vue';
import VueThumbnail from '../../components/thumbnail.vue';
import VuePricing from '../../components/job/pricing.vue';
import VueTagsDropdown from '../../components/job/tags-dropdown.vue';
import VueTagsSkill from '../../components/job/tag-skill.vue';
import VueGooglePlace from '../../components/google-maps/place.vue';
import VueText from '../../components/forms/text.vue';
import VueSelect from '../../components/forms/select.vue';
import VueCheckbox from '../../components/forms/checkbox.vue';
import VueRadio from '../../components/forms/radio.vue';
import VueError from '../../components/forms/error.vue';
import VueButton from '../../components/forms/button.vue';
import VueModal from '../../components/modal.vue';
import VueMap from '../../components/google-maps/map.vue';
import VueMarker from '../../components/google-maps/marker.vue';
import Editor from '@tinymce/tinymce-vue';
import axios from "axios";

new Vue({
  el: '.submit-form',
  delimiters: ['${', '}'],
  data: Object.assign(window.data, {isSubmitting: false, isDone: 0, showFormNavbar: false}),
  components: {
    'vue-tinymce': Editor,
    'vue-thumbnail': VueThumbnail,
    'vue-pricing': VuePricing,
    'vue-tags-dropdown': VueTagsDropdown,
    'vue-tag-skill': VueTagsSkill,
    'vue-google-place': VueGooglePlace,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-checkbox': VueCheckbox,
    'vue-radio': VueRadio,
    'vue-error': VueError,
    'vue-modal': VueModal,
    'vue-button': VueButton,
    'vue-map': VueMap,
    'vue-marker': VueMarker
  },

  mounted() {
    this.marker = null;

    window.addEventListener('scroll', this.handleScroll);
    document.querySelector('[v-loader]')?.remove();
  },
  destroyed() {
    window.removeEventListener('scroll', this.handleScroll);
  },
  methods: {
    submitForm() {
      this.isSubmitting = true;

      // nextTick() is required in order to reload data in the form before calling FormData() to aggregate inputs
      this.$nextTick(() => {
        axios.post(this.$refs.submitForm.action, new FormData(this.$refs.submitForm))
          .then(response => {
            window.location.href = response.data;
          })
          .catch(error => {
            this.errors = error.response.data.errors;

            window.location.href = '#top';
          })
          .finally(() => {
            this.isSubmitting = false;
          });
      });
    },

    /**
     * Add tag after clicking on suggestion tag.
     *
     * @param {String} name
     */
    addTag(name) {
      this.job.tags.push({name: name, pivot: {priority: 1}});
      // fetch only tag name
      let pluck = this.job.tags.map(item => item.name);

      // request suggestions
      axios.get(this.suggestion_url, {params: {t: pluck}})
        .then(response => {
          this.suggestions = response.data;
        });
    },

    removeTag(name) {
      let index = this.job.tags.findIndex(el => el.name === name);

      this.job.tags.splice(index, 1);
    },

    isInvalid(fields) {
      return Object.keys(this.errors).findIndex(element => fields.indexOf(element) > -1) > -1;
    },

    charCounter(item, limit) {
      let model = item.split('.').reduce((o, i) => o[i], this);

      return limit - String(model !== null ? model : '').length;
    },

    toggleBenefit(item) {
      let index = this.firm.benefits.indexOf(item);

      if (index === -1) {
        this.firm.benefits.push(item);
      } else {
        this.firm.benefits.splice(index, 1);
      }
    },

    addBenefit(e) {
      if (e.target.value.trim()) {
        this.firm.benefits.push(e.target.value);
      }

      e.target.value = '';
    },

    removeBenefit(benefit) {
      this.firm.benefits.splice(this.firm.benefits.indexOf(benefit), 1);
    },

    /**
     * Enable/disable feature for this offer.
     *
     * @param feature
     */
    toggleFeature(feature) {
      feature.pivot.checked = +!feature.pivot.checked;
    },

    addFirm() {
      this.$refs['add-firm-modal'].open();
    },

    selectFirm(firmId) {
      let index = this.firms.findIndex(element => element.id === firmId);

      this.firm = this.firms[index];
      this.firm.is_private = false;

      // text can not be NULL
      // tinymce.get('description').setContent(this.firm.description === null ? '' : this.firm.description);
      this.firm.description = this.firm.description === null ? '' : this.firm.description;

      this.$nextTick(() => {
        $('#industries').trigger('chosen:updated');
      });
    },

    _newFirm() {
      this.$refs['add-firm-modal'].close();

      this.firm = Object.assign(this.firm, {
        id: null,
        name: '',
        logo: {filename: null, url: null},
        thumbnail: null,
        description: '',
        website: null,
        is_private: false,
        is_agency: false,
        employees: null,
        founded: null,
        vat_id: null,
        youtube_url: null,
        gallery: [{file: ''}],
        benefits: [],
        industries: [],
        latitude: null,
        longitude: null,
        country: null,
        street: null,
        postcode: null,
        city: null,
        street_number: null,
        country_id: null
      });
    },

    changeAddress(e) {
      const val = e.target.value.trim();
      const geocoder = new Geocoder();

      if (val.length) {
        geocoder.geocode(val, result => {
          this.firm = Object.assign(this.firm, result);
        });
      } else {
        ['longitude', 'latitude', 'country', 'city', 'street', 'street_number', 'postcode'].forEach(field => {
          this.firm[field] = null;
        });
      }
    },

    geocode(latlng) {
      const geocoder = new Geocoder();

      geocoder.reverseGeocode(latlng, result => {
        this.firm = Object.assign(this.firm, result);
      });
    },

    addPhoto(file) {
      this.firm.gallery.splice(this.firm.gallery.length - 1, 0, file);
    },

    removePhoto(file) {
      let index = this.firm.gallery.findIndex(photo => photo.file === file);

      if (index > -1) {
        this.firm.gallery.splice(index, 1);
      }
    },

    addLocation() {
      this.job.locations.push({});
    },

    removeLocation(location) {
      this.job.locations.splice(this.job.locations.indexOf(location), 1);
    },

    formatAddress(index, data) {
      const strip = (value) => value !== undefined ? value : '';

      data.label = [(`${strip(data.street)} ${strip(data.street_number)}`).trim(), data.city, data.country]
        .filter(item => item !== '')
        .join(', ');

      this.$set(this.job.locations, index, data);
    },

    addLogo(result) {
      this.firm.logo = result;
    },

    removeLogo() {
      this.firm.logo = {url: null, filename: null};
    },

    handleScroll() {
      const offset = document.getElementById('footer-top').offsetTop;

      this.showFormNavbar = ((window.scrollY + window.innerHeight) < offset);
    }
  },
  computed: {
    address() {
      return String((this.firm.street || '') + ' ' + (this.firm.street_number || '') + ' ' + (this.firm.postcode || '') + ' ' + (this.firm.city || '')).trim();
    },

    gallery() {
      return this.firm.gallery && this.firm.gallery.length ? this.firm.gallery : {'file': ''};
    },

    tinymceOptions() {
      return tinymce;
    },

    // @todo refaktoring?
    isPrivate: {
      get() {
        return +this.firm.is_private;
      },
      set(val) {
        this.firm.is_private = val;
      }
    },

    isAgency: {
      get() {
        return +this.firm.is_agency;
      },
      set(val) {
        this.firm.is_agency = val;
      }
    },

    enableApply: {
      get() {
        return +this.job.enable_apply;
      },
      set(val) {
        this.job.enable_apply = val;
      }
    },


  },
  watch: {
    'firm.is_private'(flag) {
      if (!Boolean(parseInt(flag))) {
        google.maps.event.trigger(map, 'resize');
      }
    },

    'firm.is_agency'() {
      this.showFormNavbar = false;
    }
  }
});

import VuePricing from '../../components/job/pricing.vue';

Vue.component('vue-pricing', VuePricing);

new Vue({
  el: '#business',
  delimiters: ['${', '}'],
  data: window.data
});

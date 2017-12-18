import VuePricing from '../../components/pricing.vue';

Vue.component('vue-pricing', VuePricing);

let vm = new Vue({
    el: '#business',
    delimiters: ['${', '}'],
    data: window.data
});

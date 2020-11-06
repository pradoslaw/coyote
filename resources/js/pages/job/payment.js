import Vue from 'vue';
import VueMaskedInput from 'vue-masked-input';
import VueFormGroup from '@/js/components/forms/form-group.vue';
import VueText from '@/js/components/forms/text.vue';
import VueSelect from '@/js/components/forms/select.vue';
import VueCheckbox from '@/js/components/forms/checkbox.vue';
import VueButton from '@/js/components/forms/button.vue';
import axios from 'axios';
import VueNotifications from "vue-notification";
import {default as axiosErrorHandler} from "@/js/libs/axios-error-handler";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  el: '#js-payment',
  delimiters: ['${', '}'],
  components: {
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-checkbox': VueCheckbox,
    'vue-masked-input': VueMaskedInput,
    'vue-button': VueButton
  },
  filters: {
    money(value) {
      return parseFloat(value).toFixed(2);
    }
  },
  data: {
    countries: window.countries,
    calculator: window.calculator,
    varRates: window.vat_rates,
    form: window.form,
    banks: window.banks,
    coupon: {
      code: null,
      amount: 0
    },
    enableInvoice: true,
    isCoupon: false,
    errors: {},
    isProcessing: false
  },
  methods: {
    calculate() {
      // if VAT ID is empty we must add VAT
      this.calculator.vat_rate = this.vat_rates[this.form.invoice.country_id];
    },
    submitForm(e) {
      const data = Object.assign(this.form, {price: this.grossPrice, enable_invoice: this.grossPrice > 0 ? this.enableInvoice : false});

      this.errors = {};
      this.isProcessing = true;

      axios.post(window.location.href, data)
        .then(result => {
          window.location.href = result.data;
        })
        .catch(err => {
          if (err.response.status !== 422) {
            return;
          }

          this.errors = err.response.data.errors;
        })
        .finally(() => this.isProcessing = false);
    },
    setPaymentMethod(method) {
      this.form.payment_method = method;
    }
  },
  computed: {
    percentageVatRate() {
      return (this.calculator.vat_rate * 100) - 100;
    },
    netPrice() {
      return this.calculator.net_price;
    },
    grossPrice() {
      return this.discountNetPrice * this.calculator.vat_rate;
    },
    discountNetPrice() {
      return Math.max(0, this.calculator.net_price - this.coupon.amount);
    },
    vatPrice() {
      return this.grossPrice - this.discountNetPrice;
    }
  },
  watch: {
    'coupon.code': function(newValue) {
      axios.get('/Praca/Coupon/Validate', {params: {code: newValue}}).then(result => {
        this.coupon.amount = result.data;
        this.form.coupon = newValue;
      });
    }
  }
});

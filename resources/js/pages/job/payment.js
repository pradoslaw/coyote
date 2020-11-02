import Vue from 'vue';
import VueMaskedInput from 'vue-masked-input';
import VueFormGroup from '@/js/components/forms/form-group.vue';
import VueText from '@/js/components/forms/text.vue';
import VueSelect from '@/js/components/forms/select.vue';
import VueCheckbox from '@/js/components/forms/checkbox.vue';
import axios from 'axios';

new Vue({
  el: '#js-payment',
  delimiters: ['${', '}'],
  components: {
    'vue-form-group': VueFormGroup,
    'vue-text': VueText,
    'vue-select': VueSelect,
    'vue-checkbox': VueCheckbox,
    'vue-masked-input': VueMaskedInput
  },
  data: {
    countries: window.countries,
    calculator: window.calculator,
    varRates: window.vat_rates,
    defaultVatRate: window.default_vat_rate,
    form: window.form,
    banks: window.banks,
    coupon: {
      code: null,
      amount: 0
    },
    enableInvoice: true,
    isCoupon: false
  },
  methods: {
    money(value) {
      return parseFloat(value).toFixed(2);
    },
    calculate() {
      // if VAT ID is empty we must add VAT
      this.calculator.vat_rate = !this.form.invoice.vat_id ?
        this.default_vat_rate
          : (this.form.invoice.country_id ? this.vat_rates[this.form.invoice.country_id] : this.default_vat_rate);
    },
    submit(e) {

      this.$nextTick(() => {
        HTMLFormElement.prototype.submit.call(e.target);
      });
    },
    setPaymentMethod(payment_method) {
      this.form.payment_method = payment_method;
    }
  },
  computed: {
    percentageVatRate() {
      return (this.calculator.vat_rate * 100) - 100;
    },
    netPrice() {
      return this.money(this.calculator.net_price);
    },
    grossPrice() {
      return this.money(this.discountNetPrice * this.calculator.vat_rate);
    },
    discountNetPrice() {
      return this.money(Math.max(0, this.calculator.net_price - this.coupon.amount));
    },
    vatPrice() {
      return this.money(this.grossPrice - this.discountNetPrice);
    }
  },
  watch: {
    'coupon.code': function(newValue) {
      axios.get('/Praca/Coupon/Validate', {params: {code: newValue}}).then(result => this.coupon.amount = result.data);
    }
  }
});

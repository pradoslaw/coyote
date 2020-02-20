import 'jquery.maskedinput/src/jquery.maskedinput';
import Config from '../../libs/config';

let vm = new Vue({
  el: '#payment-form',
  delimiters: ['${', '}'],
  // data might be undefined on gateway page
  data: 'data' in window ? window.data : {},
  methods: {
    money: function (value) {
      return parseFloat(value).toFixed(2);
    },
    calculate: function () {
      // if VAT ID is empty we must add VAT
      if (this.form.invoice.vat_id === undefined || this.form.invoice.vat_id === null || this.form.invoice.vat_id.length === 0) {
        this.calculator.vat_rate = this.default_vat_rate;
      } else {
        this.calculator.vat_rate = this.form.invoice.country_id ? this.vat_rates[this.form.invoice.country_id] : this.default_vat_rate;
      }
    },
    submit: function (e) {
      this.form.cvc = $('#cvc').val();
      this.form.number = $('#credit-card').val();

      this.$nextTick(() => {
        HTMLFormElement.prototype.submit.call(e.target);
      });
    },
    validateCoupon: function (e) {
      $.get(Config.get('validate_coupon_url'), {code: e.target.value}, result => {
        if (typeof result.id !== 'undefined') {
          this.coupon = result;
        } else {
          this.coupon = {code: null, amount: 0};
        }
      });
    },
    setPaymentMethod: function (payment_method) {
      this.form.payment_method = payment_method;
    }
  },
  computed: {
    percentageVatRate: function () {
      return (this.calculator.vat_rate * 100) - 100;
    },
    netPrice: function () {
      return this.money(this.calculator.net_price);
    },
    grossPrice: function () {
      return this.money(this.discountNetPrice * this.calculator.vat_rate);
    },
    discountNetPrice: function () {
      return this.money(Math.max(0, this.calculator.net_price - this.coupon.amount));
    },
    vatPrice: function () {
      return this.money(this.grossPrice - this.discountNetPrice);
    }
  }
});

$(function ($) {
  $('#enable-invoice')
    .change(function () {
      $('.invoice').toggle($(this).is(':checked'));
    })
    .trigger('change');

  $("#credit-card").mask("9999-9999-9999-9999", {
    completed: function () {
      vm.$set(vm.form, 'number', this.val());
    }
  });

  $("#cvc").mask("999");
});

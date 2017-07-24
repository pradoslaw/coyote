import 'jquery.maskedinput/src/jquery.maskedinput';
import Config from '../../libs/config';

let vm = new Vue({
    el: '#payment',
    delimiters: ['${', '}'],
    data: window.data,
    mounted: function () {

    },
    methods: {
        money: function(value) {
            return parseFloat(value).toFixed(2);
        },
        calculate: function(select) {
            this.calculator.vat_rate = select.target.value ? this.vat_rates[select.target.value] : this.default_vat_rate;
        },
        submit: function(e) {
            this.form.cvc = $('#cvc').val();
            this.form.number = $('#credit-card').val();

            let client = new braintree.api.Client({clientToken: this.client_token});

            client.tokenizeCard({
                number: this.form.number,
                cardholderName: this.form.name,
                expirationMonth: this.form.expiration_month,
                expirationYear: this.form.expiration_year,
                cvv: this.form.cvc,
            }, (err, nonce) => {
                this.form.payment_method_nonce = nonce;

                this.$nextTick(() => {
                    HTMLFormElement.prototype.submit.call(e.target);
                });
            });
        },
        validateCoupon: function(e) {
            $.get(Config.get('validate_coupon_url'), {code: e.target.value}, result => {
                if (typeof result.id !== 'undefined') {
                    this.coupon = result;
                } else {
                    this.coupon = {code: null, amount: 0};
                }
            });
        }
    },
    computed: {
        percentageVatRate: function() {
            return (this.calculator.vat_rate * 100) - 100;
        },
        netPrice: function() {
            return this.money(this.calculator.net_price);
        },
        grossPrice: function() {
            return this.money(this.discountNetPrice * this.calculator.vat_rate);
        },
        discountNetPrice: function() {
            return this.money(Math.max(0, this.calculator.net_price - this.coupon.amount));
        },
        vatPrice: function() {
            return this.money(this.grossPrice - this.discountNetPrice);
        }
    }
});

$(function($) {
    $('#enable-invoice')
        .change(function() {
            $('.invoice').toggle($(this).is(':checked'));
        })
        .trigger('change');

    $("#credit-card").mask("9999-9999-9999-9999", {completed: function () {
        vm.$set(vm.form, 'number', this.val());
    }});

    $("#cvc").mask("999");
});

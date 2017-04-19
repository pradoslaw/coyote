import 'jquery.maskedinput/src/jquery.maskedinput';

new Vue({
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
            let client = new braintree.api.Client({clientToken: this.client_token});

            client.tokenizeCard({
                number: this.form.number,
                cardholderName: this.form.name,
                expirationMonth: this.form.expiration_month,
                expirationYear: this.form.expiration_year,
                cvv: this.form.cvv,
            }, (err, nonce) => {
                this.form.payment_method_nonce = nonce;

                this.$nextTick(() => {
                    HTMLFormElement.prototype.submit.call(e.target);
                });
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
            return this.money(this.netPrice * this.calculator.vat_rate);
        },
        vatPrice: function() {
            return this.money(this.grossPrice - this.netPrice);
        }
    }
});

$(function($) {
    $('#enable-invoice')
        .change(function() {
            $('.invoice').toggle($(this).is(':checked'));
        })
        .trigger('change');

    $("#credit-card").mask("9999-9999-9999-9999");
    $("#cvc").mask("999");
});

import 'jquery.maskedinput/src/jquery.maskedinput';

new Vue({
    el: '#payment-form',
    delimiters: ['${', '}'],
    data: window.data,
    mounted: function () {

    },
    methods: {
        money: function(value) {
            return parseFloat(value).toFixed(2);
        },
        calculate: function() {
            console.log(this.form.invoice.country_id);
        }
    },
    computed: {
        vatRate: function() {
            return (this.calculator.vat_rate * 100) - 100;
        },
        netPrice: function() {
            return this.money(this.calculator.net_price);
        },
        grossPrice: function() {
            return this.money(this.calculator.net_price * this.calculator.vat_rate);
        },
        vatPrice: function() {
            return this.money(this.grossPrice - this.calculator.net_price);
        },
        price: function() {
            return this.money(this.grossPrice * this.exchange_rate);
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

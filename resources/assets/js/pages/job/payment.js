import 'jquery.maskedinput/src/jquery.maskedinput';

$(function($) {
    $('#enable-invoice')
        .change(function() {
            $('.invoice').toggle($(this).is(':checked'));
        })
        .trigger('change');

    $("#credit-card").mask("9999-9999-9999-9999");
    $("#cvc").mask("999");

    $('.fa-question-circle').tooltip();
});

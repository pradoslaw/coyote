$(function() {
    'use strict';

    $.fn.disable = function() {
        var origin = this.html();

        this.html('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + origin).attr({'disabled': 'disabled', 'data-origin': origin});
    };

    $.fn.enable = function() {
        this.html(this.data('origin')).removeAttr('disabled').removeAttr('data-origin');
    };

    $('form').submit(function() {
        var submit = $(this).find(':submit');

        if (typeof submit.data('submit-state') !== 'undefined') {
            submit.disable();
        }
    });
});

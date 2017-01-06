$(function() {
    'use strict';

    $.fn.disable = function() {
        let origin = this.html();

        this.html('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + origin).attr({'disabled': 'disabled', 'data-origin': origin});
    };

    $.fn.enable = function() {
        this.html(this.data('origin')).removeAttr('disabled').removeAttr('data-origin');
    };

    $('form').on('submit', function() {
        let submit = $(this).find(':submit');

        if (typeof submit.data('submit-state') !== 'undefined') {
            submit.disable();
        }
    });
});

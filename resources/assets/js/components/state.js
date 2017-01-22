$(function() {
    'use strict';

    $.fn.disable = function() {
        if (this.attr('disabled') == 'disabled') {
            return;
        }

        let origin, text = this.html();

        if (typeof this.data('submit-state') !== 'undefined') {
            text = this.data('submit-state');
        }

        this.html(`<i class="fa fa-spinner fa-spin fa-fw"></i> ${text}`).attr({'disabled': 'disabled', 'data-origin': origin});
    };

    $.fn.enable = function() {
        this.html(this.data('origin')).removeAttr('disabled').removeAttr('data-origin');
    };

    $(document).on('submit', 'form', function() {
        let submit = $(this).find(':submit');

        if (typeof submit.data('submit-state') !== 'undefined') {
            submit.disable();
        }
    });
});

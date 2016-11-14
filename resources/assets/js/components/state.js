$(function() {
    'use strict';

    $('form').submit(function() {
        var submit = $(this).find(':submit');

        if (typeof submit.data('submit-state') !== 'undefined') {
            submit.html('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + submit.data('submit-state')).attr('disabled', 'disabled');
        }
    });
});

$(function() {
    'use strict';

    $('form').submit(function() {
        var submit = $(this).find(':submit');

        if (typeof submit.data('submit-state') !== 'undefined') {
            submit.text(submit.data('submit-state')).attr('disabled', 'disabled');
        }
    });
});
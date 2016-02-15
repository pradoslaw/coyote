$(function() {
    'use strict';

    var popover = Session.getItem('popover');

    if (popover === null) {
        popover = [];
    } else {
        popover = JSON.parse(popover);
    }

    $('.alert-popover').each(function() {
        if ($.inArray($(this).data('id'), popover) === -1) {
            $(this).fadeIn(400);
        }
    }).on('click', '.close', function() {
        popover.push($(this).parent().data('id'));
        Session.setItem('popover', JSON.stringify(popover));

        $(this).parent().hide();
    });
});
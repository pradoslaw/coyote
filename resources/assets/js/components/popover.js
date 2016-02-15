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
            if ($(this).data('containment')) {
                var p = $($(this).data('containment')).offset();

                if ($(this).hasClass('bottom')) {
                    $(this).css({'left': p.left, 'top': p.top - $(this).outerHeight() - 15});
                }
            }
            $(this).fadeIn(400);
        }
    }).on('click', '.close', function() {
        popover.push($(this).parent().data('id'));
        Session.setItem('popover', JSON.stringify(popover));

        $(this).parent().hide();
    });
});
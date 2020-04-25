import Config from '../libs/config';

$(function () {
    'use strict';

    let toolTipTimer;

    $('body').delegate('a[data-user-id]', 'mouseenter mouseleave', function (e) {
        // jezeli link jest umieszczony w komponencie vcard - pomijamy dalsze czynnosci
        if ($(this).parent().is('#vcard-header')) {
            return;
        }

        clearTimeout(toolTipTimer);

        if (e.type === 'mouseenter') {
            let userId = $(this).data('user-id');

            toolTipTimer = setTimeout(function () {
                $.ajax({
                    type: 'GET',
                    url: `${Config.get('public')}/User/Vcard/${userId}`,
                    dataType: 'html',
                    crossDomain: true,
                    xhrFields: {
                        withCredentials: true
                    },
                    success: function (html) {
                        $('#vcard').remove();

                        $(html).css({
                            top: e.pageY + 17,
                            left: Math.min(e.pageX + 10, $(window).width() - 450)
                        })
                        .appendTo('body');
                    }
                });

            }, 800);
        }
        else if (e.type === 'mouseleave') {
            toolTipTimer = setTimeout(function () {
                $('#vcard').remove();

            }, 1500);
        }
    })
    .delegate('#vcard', 'mouseenter mouseleave', function (e) {
        if (e.type === 'mouseenter') {
            clearTimeout(toolTipTimer);
        }
        else if (e.type === 'mouseleave') {
            $('#vcard').remove();
        }
    });
});

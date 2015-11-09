$(function () {
    var toolTipTimer;

    $('body').delegate('a[data-user-id]', 'mouseenter mouseleave', function (e) {
        clearTimeout(toolTipTimer);

        if (e.type == 'mouseenter') {
            var userId = $(this).data('user-id');

            toolTipTimer = setTimeout(function () {
                $.ajax({
                    type: 'GET',
                    url: baseUrl + '/User/Vcard/' + userId,
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
        else if (e.type == 'mouseleave') {
            toolTipTimer = setTimeout(function () {
                $('#vcard').remove();

            }, 1500);
        }
    })
    .delegate('#vcard', 'mouseenter mouseleave', function (e) {
        if (e.type == 'mouseenter') {
            clearTimeout(toolTipTimer);
        }
        else if (e.type == 'mouseleave') {
            $('#vcard').remove();
        }
    });
});
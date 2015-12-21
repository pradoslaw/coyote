var SCREEN_MD = 1024;

$(function () {
    'use strict';

    function toggleSidebar(flag) {
        $('#btn-toggle-sidebar').toggleClass('sidebar-hidden', !flag);
        $('#sidebar').toggle(flag);
        $('#index').toggleClass('sidebar', flag).children('.btn-watch-xs, .btn-atom-xs, .btn-mark-read-xs').toggleClass('show', !flag);
    }

    if ($('#sidebar').is(':hidden')) {
        $('#index').children('.btn-watch-xs, .btn-atom-xs, .btn-mark-read-xs').addClass('show');
    }

    $(document).click(function (e) {
        var container = $('#sidebar, #btn-toggle-sidebar');

        if ($('#sidebar').css('position') == 'absolute' && !container.is(e.target) && container.has(e.target).length == 0) {
            $('#sidebar').hide();
        }
    });

    if ($(window).width() <= SCREEN_MD) {
        $('#btn-toggle-sidebar').addClass('sidebar-hidden');
    }

    $('#btn-toggle-sidebar').click(function () {
        if ($(window).width() <= SCREEN_MD) {
            $('#sidebar').toggle();

            var handler = function () {
                if ($(window).width() > SCREEN_MD) {
                    if ($('#index').hasClass('sidebar')) {
                        toggleSidebar(true);
                        $(window).unbind('resize', handler);
                    }
                }
            };

            $(window).unbind('resize', handler).bind('resize', handler);
        }
        else {
            var flag = $('#index').hasClass('sidebar');
            toggleSidebar(!flag);

            //$.ajax(
            //    {
            //        type: 'POST',
            //        url: baseUrl + 'User/Setting/__save',
            //        data: {'forum_sidebar': !flag},
            //        dataType: 'html',
            //        crossDomain: true,
            //        xhrFields:
            //        {
            //            withCredentials: true
            //        }
            //    });
        }
    });

    /**
     * Collapse forum category
     */
    $('.toggle[data-toggle="collapse"]').click(function() {
        $(this).toggleClass('in');
    });

    /**
     * Show "flag to report" page
     */
    $('.btn-report').click(function() {
        var metadata = {'post_id': $(this).data('post-id')};

        $.get(baseUrl + '/Flag', {url: $(this).data('url'), metadata: JSON.stringify(metadata)}, function(html) {
            $(html).appendTo('body');

            $('#flag').find('.modal').modal('show');
        });
    });

    /**
     * Delete post/topic
     */
    $('.btn-del').click(function() {
        var $this = $(this);

        $('#confirm-delete').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Usuwanie...');
            window.location = $this.attr('href');
        });

        return false;
    });

    if ('onhashchange' in window) {
        var onHashChange = function () {
            var hash = window.location.hash;

            if (hash.substring(1, 3) === 'id') {
                var object = $(hash).parents('.post');

                object.css('background-color', '#FFDCA5');
                $('#container-fluid').one('mousemove', function () {
                    object.animate({backgroundColor: '#FFF'}, 1500);
                });
            }
        };

        window.onhashchange = onHashChange;
        onHashChange();
    }
});

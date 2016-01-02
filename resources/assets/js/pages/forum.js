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

        if ($('#sidebar').css('position') === 'absolute' && !container.is(e.target) && container.has(e.target).length == 0) {
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

    function toPost(url) {
        var form = $('<form>', {'method': 'POST', 'action': url});
        form.append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">');

        return form;
    }

    function error(text) {
        $('#alert').modal('show');
        $('#alert').find('.modal-body').text(text);
    }

    /**
     * Restore deleted post
     */
    $('.btn-res').click(function() {
        toPost($(this).attr('href')).submit();

        return false;
    });

    /**
     * Delete post/topic
     */
    $('.btn-del').click(function() {
        var $this = $(this);

        $('#confirm-delete').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Usuwanie...');
            var modal = $(this).parents('.modal-content');

            var form = toPost($this.attr('href'));
            form.append('<input type="hidden" name="reason" value="' + $('select', modal).val() + '">');
            form.submit();
        });

        return false;
    });

    /**
     * Change forum category
     */
    $('#sel-forum-list').change(function() {
        window.location.href = forumUrl + '/' + $(this).val();
    });

    /**
     * Refresh forum category
     */
    $('#btn-goto').click(function() {
        $('#sel-forum-list').trigger('change');
    });

    $('.comments').find('textarea').one('focus', function() {
        $(this).prompt(promptUrl).fastSubmit().autogrow().focus();
    });

    $('.comments').on('submit', 'form', function() {
        var $form = $(this);
        $('button', $form).attr('disabled', 'disabled').text('Wysyłanie...');

        $.post($form.attr('action'), $form.serialize(), function(html) {
            $('textarea', $form).val('');
            $form.collapse('hide');

            $(html).hide().insertBefore($form).show('slow');
        })
        .always(function() {
            $('button', $form).removeAttr('disabled').text('Zapisz komentarz');
        })
        .error(function(event, jqxhr) {
            if (typeof event.responseJSON.text !== 'undefined') {
                error(event.responseJSON.text);
            } else if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    });


    $('.comments form').on('shown.bs.collapse', function() {
        $(this).find('textarea').focus();
        $('.btn-comment[href="#' + $(this).attr('id') + '"]').addClass('active');
    })
    .on('hidden.bs.collapse', function() {
        $('.btn-comment[href="#' + $(this).attr('id') + '"]').removeClass('active');
    });

    /**
     * Append list of reasons (of moderator actions) to modal box
     */
    if (typeof reasonsList !== 'undefined') {
        var select = $('<select>', {'class': 'form-control input-sm'});
        select.append('<option>-- wybierz powód --</option>');

        $.each(reasonsList, function(key, value) {
            select.append('<option value="' + key + '">' + value + '</option>');
        });

        $('#confirm-delete').each(function() {
            select.appendTo($('.modal-body', this));
        });
    }

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

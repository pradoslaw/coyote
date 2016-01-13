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
     * Change limit of posts/topics shown on one page
     */
    $('select[name="perPage"]').change(function() {
        window.location.href = $(this).data('url') + '?perPage=' + $(this).val();
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
     * Subscribe/unsubscribe topic (sidebar option)
     */
    $('.btn-watch a').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function(count) {
            $this.parent().toggleClass('on');

            $this.find('span').text($this.parent().hasClass('on') ? 'Zakończ obserwację' : 'Obserwuj');
            $this.find('small').text('(' + count + ' ' + declination(count, ['obserwujący', 'obserwujących', 'obserwujących']) + ')');
        })
        .error(function(event) {
            if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    });

    $('#btn-lock a').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function() {
            $this.parent().toggleClass('on');
            $this.text($this.parent().hasClass('on') ? 'Odblokuj wątek' : 'Zablokuj wątek');
        })
        .error(function(event) {
            if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    });

    $('#btn-move ul a').click(function() {
        var $this = $(this);

        $('#confirm-move').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Przenoszenie...');
            var modal = $(this).parents('.modal-content');

            var form = toPost($this.attr('href'));
            if ($('select', modal).length) {
                form.append('<input type="hidden" name="reason" value="' + $('select', modal).val() + '">');
            }

            form.append('<input type="hidden" name="path" value="' + $this.data('path') + '">');
            form.submit();
        });

        return false;
    });

    /**
     * Mark category/categories as read
     */
    $('.btn-mark-read a').click(function() {
        $('.btn-view').removeClass('unread');
        $('.ico').each(function() {
            if ($(this).hasClass('new')) {
                $(this).removeClass('new').addClass('normal');
            }
        });

        return false;
    });

    /**
     * Mark category/topic as read by clicking on it
     */
    $('.new').click(function() {
        $(this).addClass('normal').removeClass('new');
        $(this).parent().next().find('.btn-view').removeClass('unread');
    });

    /**
     * Subscribe/unsubscribe topic (from topics list)
     */
    $('.btn-watch-sm').click(function() {
        $(this).toggleClass('on');
        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Subscribe/unsubscribe post
     */
    $('.btn-sub').click(function() {
        $(this).toggleClass('active');
        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Delete post/topic
     */
    $('.btn-del').click(function() {
        var $this = $(this);

        $('#post-confirm-delete').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Usuwanie...');
            var modal = $(this).parents('.modal-content');

            var form = toPost($this.attr('href'));
            if ($('select', modal).length) {
                form.append('<input type="hidden" name="reason" value="' + $('select', modal).val() + '">');
            }

            form.submit();
        });

        return false;
    });

    /**
     * Share post link
     */
    $('.btn-share').one('click', function() {
        var url = $(this).attr('href');
        var $input = $('<input type="text" class="form-control input-sm" style="width: 300px" value="' + url + '" />');

        $input.click(function() {
            this.select();
        });

        $(this).popover({
            'html': true,
            'content': $input,
            'title': '',
            'container': 'body'
        }).tooltip('destroy');
    })
    .click(function() {
        $(this).popover('show');

        return false;
    });

    $('body').on('click', function (e) {
        $('.btn-share').each(function () {
            //the 'is' for buttons that trigger popups
            //the 'has' for icons within a button that triggers a popup
            if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
                $(this).popover('hide');
            }
        });
    });

    $('.vote-up').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function(json) {
            $this.toggleClass('on');
            $this.prev().text(json.count);
        })
            .error(function(event) {
                if (typeof event.responseJSON.error !== 'undefined') {
                    error(event.responseJSON.error);
                }
            });

        return false;
    });

    $('.vote-accept[href]').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function() {
            $this.toggleClass('on');
            $('.vote-accept').not($this).removeClass('on');
        })
            .error(function(event) {
                if (typeof event.responseJSON.error !== 'undefined') {
                    error(event.responseJSON.error);
                }
            });

        return false;
    });

    $('#btn-fast-reply').click(function() {
        $('#box-fast-form').find('textarea').focus();
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

    var comments = {};

    $('.comments').on('submit', 'form', function() {
        var $form = $(this);
        $('button', $form).attr('disabled', 'disabled').text('Wysyłanie...');
        $('textarea', $form).attr('readonly', 'readonly');

        $.post($form.attr('action'), $form.serialize(), function(html) {
            if ($form.hasClass('collapse')) {
                $('textarea', $form).val('');
                $form.collapse('hide');

                $(html).hide().insertBefore($form).show('slow');
            } else {
                $form.parent().hide().replaceWith(html).show('slow');
            }
        })
        .always(function() {
            $('button', $form).removeAttr('disabled').text('Zapisz komentarz');
            $('textarea', $form).removeAttr('readonly');
        })
        .error(function(event, jqxhr) {
            if (typeof event.responseJSON.text !== 'undefined') {
                error(event.responseJSON.text);
            } else if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            }
        });

        return false;
    })
    .on('click', '.btn-comment-del', function() {
        var $this = $(this);

        $('#comment-confirm-delete').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Usuwanie...');

            $.post($this.attr('href'), function() {
                $('#comment-confirm-delete').modal('hide');

                $this.parent().fadeOut(function() {
                    $(this).remove();
                });
            })
            .error(function(event) {
                if (typeof event.responseJSON.error !== 'undefined') {
                    error(event.responseJSON.error);
                }
            });
        });

        return false;
    })
    .on('click', '.btn-show-all', function() {
        $(this).nextAll('div:hidden').fadeIn(1000);
        $(this).remove();
    })
    .on('keyup', 'textarea', function() {
        if (parseInt($(this).val().length) > 580) {
            $(this).val($(this).val().substr(0, 580));
        }

        $('strong', $(this).parents('form')).text(580 - parseInt($(this).val().length));
    })
    .on('click', '.btn-comment-edit', function() {
        var $comment = $(this).parent();

        $.get($(this).attr('href'), function(html) {
            comments[$comment.data('comment-id')] = $comment.html();
            $comment.html(html).find('textarea').prompt(promptUrl).fastSubmit().autogrow().focus();
        });

        return false;
    })
    .on('click', '.btn-reset', function() {
        var $comment = $(this).parent().parent();

        $comment.html(comments[$comment.data('comment-id')]);
    })
    .find('textarea').one('focus', function() {
        $(this).prompt(promptUrl).fastSubmit().autogrow().focus();
    });


    /**
     * Show/hide new comment's form
     */
    $('.comments form').on('shown.bs.collapse', function() {
        $(this).find('textarea').focus();
        $('.btn-comment[href="#' + $(this).attr('id') + '"]').addClass('active');
    })
    .on('hidden.bs.collapse', function() {
        $('.btn-comment[href="#' + $(this).attr('id') + '"]').removeClass('active');
    });

    var posts = {};

    $('.btn-fast-edit').click(function() {
        var $this = $(this);
        var $post = $('.post-content[data-post-id="' + $this.data('post-id') + '"]');

        if (!$this.hasClass('active')) {
            $.get($this.attr('href'), function(html) {
                posts[$this.data('post-id')] = $post.html();
                $post.html(html).find('textarea').prompt(promptUrl).fastSubmit().autogrow().focus();

                $this.addClass('active');
            });
        } else {
            $post.html(posts[$this.data('post-id')]);
            $this.removeClass('active');
        }

        return false;
    });

    $('.post-content').on('submit', 'form', function() {
        var $form = $(this);
        var $post = $(this).parent();

        $(':submit', $form).attr('disabled', 'disabled').text('Zapisywanie...');
        $('textarea', $form).attr('readonly', 'readonly');

        $.post($form.attr('action'), $form.serialize(), function(html) {
            $post.html(html);
            $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');
        })
        .error(function(event) {
            $(':submit', $form).removeAttr('disabled').text('Zapisz');
            $('textarea', $form).removeAttr('readonly');

            if (typeof event.responseJSON.error !== 'undefined') {
                error(event.responseJSON.error);
            } else if (typeof event.responseJSON.text !== 'undefined') {
                error(event.responseJSON.text);
            }
        });

        return false;
    })
        .on('click', '.btn-reset', function() {
            var $post = $(this).parent();

            $post.html(posts[$post.data('post-id')]);
            $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');
        });

    /**
     * Append list of reasons (of moderator actions) to modal box
     */
    if (typeof reasonsList !== 'undefined') {
        var select = $('<select>', {'class': 'form-control input-sm'});
        select.append('<option value="0">-- wybierz powód --</option>');

        $.each(reasonsList, function(key, value) {
            select.append('<option value="' + key + '">' + value + '</option>');
        });

        $('#post-confirm-delete, #confirm-move').each(function() {
            select.appendTo($('.modal-body', this));
        });
    }

    if ('onhashchange' in window) {
        var onHashChange = function () {
            var hash = window.location.hash;
            var object = null;
            var color = null;

            if (hash.substring(1, 3) === 'id') {
                object = $(hash).parents('.post-body');
                color = '#fff';
            } else {
                object = $(hash);

                if (object.is(':hidden')) {
                    $('div:hidden', object.parent()).show();
                    $('.btn-show-all', object.parent()).remove();
                }

                color = '#fafafa';
            }

            object.addClass('highlight').css('background-color', '#FFDCA5');
            $('#container-fluid').one('mousemove', function () {
                object.animate({backgroundColor: color}, 1500, function() {
                    $(this).removeClass('highlight');
                });
            });
        };

        window.onhashchange = onHashChange;
        onHashChange();
    }
});

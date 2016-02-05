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

            $.ajax({
                type: 'POST',
                url: baseUrl + '/User/Settings/Ajax',
                data: {'forum_sidebar': !flag},
                dataType: 'html',
                crossDomain: true,
                xhrFields: {
                    withCredentials: true
                }
            });
        }
    });

    /**
     * Show post preview
     */
    $('a[href="#preview"]').click(function(e) {
        $('#preview').find('.post-content').html('<i class="fa fa-spinner fa-spin fa-2x"></i>');

        $.post($(this).data('url'), {'text': $('#submit-form').find('textarea[name="text"]').val()}, function(html) {
            $('#preview').find('.post-content').html(html);
        });
    });

    /**
     * Collapse forum category
     */
    $('.toggle[data-toggle="collapse"]').click(function() {
        $.post($(this).data('ajax'), {flag: +$(this).hasClass('in')});
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

    /**
     * Move to another category
     */
    $('#btn-move ul a').click(function() {
        $('#modal-move').modal('show').find(':hidden[name="path"]').val($(this).data('path'));

        return false;
    });

    /**
     * Edit topic subject
     */
    $('#btn-edit-subject a').click(function() {
        $('#modal-subject').modal('show');

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

        $('.sub-unread').removeClass('sub-unread');
        $.post($(this).attr('href'));

        return false;
    });

    /**
     * Mark category/topic as read by clicking on it
     */
    $('.new').click(function() {
        $(this).addClass('normal').removeClass('new');
        $(this).parent().next().find('.btn-view').removeClass('unread');

        $.post($(this).attr('href'));

        return false;
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
    $('.post').on('click', '.btn-del', function() {
        $('#modal-post-delete').parent().attr('action', $(this).attr('href'));
        $('#modal-post-delete').modal('show');

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

    /**
     * Add to multi quote list
     */
    $('.btn-multi-quote').click(function() {
        var cookies = document.cookie.split(';');
        var cookie = [];
        var postId = parseInt($(this).data('post-id'));
        var topicId = parseInt($(this).data('topic-id'));

        var map = function(element) {
            return parseInt(element);
        };

        for (var item in cookies) {
            var name = '', value = '';
            var parts = cookies[item].split('=', 2);

            name = parts[0];
            value = parts[1];

            if ($.trim(name) === 'mqid' + topicId) {
                cookie = value.split(',').map(map);
            }
        }

        var indexOf = $.inArray(postId, cookie);
        if (indexOf === -1) {
            cookie.push($(this).data('post-id'));
        } else {
            cookie.splice(indexOf, 1);
        }

        $(this).toggleClass('active');
        document.cookie = 'mqid' + topicId + '=' + cookie.join(',') + ';path=/';
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

                $(html).insertBefore($form);
                $('.btn-sub[data-post-id="' + $(':hidden[name="post_id"]', $form).val() + '"]').addClass('active');
            } else {
                $form.parent().replaceWith(html);
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

        $('#modal-comment-delete').modal('show').one('click', '.danger', function() {
            $(this).attr('disabled', 'disabled').text('Usuwanie...');

            $.post($this.attr('href'), function() {
                $('#modal-comment-delete').modal('hide');

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
            $comment.html(html).find('textarea').prompt().fastSubmit().autogrow().focus();
        });

        return false;
    })
    .on('click', '.btn-reset', function() {
        var $comment = $(this).parent().parent();

        $comment.html(comments[$comment.data('comment-id')]);
        return false;
    })
    .find('textarea').one('focus', function() {
        $(this).prompt().fastSubmit().autogrow().focus();
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

    /**
     * Quick edit of post
     */
    var posts = {};

    $('.btn-fast-edit').click(function() {
        var $this = $(this);
        var $post = $('.post-content[data-post-id="' + $this.data('post-id') + '"]');

        if (!$this.hasClass('active')) {
            $.get($this.attr('href'), function(html) {
                posts[$this.data('post-id')] = $post.html();
                $post.html(html).find('textarea').prompt().fastSubmit().autogrow().focus();

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
    .on('click', '.btn-reset', function() {console.log(1);
        var $post = $(this).parent().parent();

        $post.html(posts[$post.data('post-id')]);
        $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');

        return false;
    });

    /**
     * Upload attachment
     */
    $('#btn-upload').click(function() {
        $('.input-file').click();
    });

    $('.input-file').change(function() {
        var $form = $('#submit-form');
        var formData = new FormData($form[0]);

        $.ajax({
            url: uploadUrl,
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function () {
                $('#btn-upload').attr('disabled', 'disabled').text('Wysyłanie...');
            },
            success: function (html) {
                $('#attachments .text-center').remove();
                $('#attachments tbody').append(html);
            },
            error: function (err) {
                $('#alert').modal('show');

                if (typeof err.responseJSON !== 'undefined') {
                    $('.modal-body').text(err.responseJSON.attachment[0]);
                }
            },
            complete: function() {
                $('#btn-upload').removeAttr('disabled').text('Dodaj załącznik');
            }
        }, 'json');

        return false;
    });

    $('#attachments').on('click', '.btn-del', function() {
        $(this).parents('tr').remove();
    })
    .on('click', '.btn-append', function() {
        var $form = $(this).parents('form');

        var parent = $(this).parents('tr');
        var file = $(':hidden', parent).val();
        var suffix = file.split('.').pop().toLowerCase();
        var markdown = '';

        if (suffix === 'png' || suffix === 'jpg' || suffix === 'jpeg' || suffix === 'gif') {
            markdown = '![' + $(this).text() + '](' + $(this).data('url') + ')';
        }

        $('textarea', $form).insertAtCaret("\n", "\n", markdown);
        $('.nav-tabs a:first').tab('show');
    });

    /**
     * Custom tags
     */
    $('#box-my-tags').on('click', '.btn-settings', function() {
        $('#box-my-tags').find('.tag-clouds').toggle();
        $('#tags-form').toggle().find('input[name="tags"]').focus();
    })
        .on('click', '.btn-add', function() {
            $('#box-my-tags').find('.btn-settings').click();
        });

    $('#tags-form').submit(function() {
        var $form = $(this);
        var tags = $('input[name="tags"]', this).val();

        tags = tags.replace(new RegExp(',', 'g'), ' ').split(' ').filter(function(element) {
            return element !== '';
        });

        $(':input', $form).attr('disabled', 'disabled');

        $.post($form.attr('action'), {'tags': tags}, function(html) {
            var object = $('#box-my-tags');

            object.find('.tag-clouds').replaceWith(html).show();
            $form.hide();
        }).always(function() {
            $(':input', $form).removeAttr('disabled');
        });

        return false;
    });

    if (jQuery.fn.pasteImage) {
        $('#submit-form textarea').pasteImage(function (textarea, html) {
                $('#attachments .text-center').remove();
                $('#attachments tbody').append(html);

                var link = $('a', html);
                textarea.insertAtCaret("\n", "\n", '![' + link.text() + '](' + link.data('url') + ')');
            })
            .wikiEditor()
            .prompt()
            .fastSubmit()
            .autogrow();
    }

    /////////////////////////////////////////////////////////////////////////////////

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

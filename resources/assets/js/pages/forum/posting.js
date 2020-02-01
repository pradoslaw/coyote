import declination from '../../components/declination';
import preventDuplicate from '../../components/prevent-duplicate';
import Dialog from "../../libs/dialog";

$(function () {
    'use strict';

    /**
     * Show post preview
     */
    $('a[href="#preview"]').click(function(e) {
        $('#preview').find('.post-content').html('<i class="fa fa-spinner fa-spin fa-2x"></i>');

        $.post($(this).data('url'), {'text': $('#submit-form').find('textarea[name="text"]').val()}, function(html) {
            $('#preview').find('.post-content').html(html);

            Prism.highlightAll();
        });
    });

    /**
     * Change limit of posts/topics shown on one page
     */
    $('select[name="perPage"]').change(function() {
        window.location.href = $(this).data('url') + '?perPage=' + $(this).val();
    });

    $(document).ajaxError(function(event, jqxhr) {
        let message;

        if (typeof jqxhr.responseJSON.errors !== 'undefined') {
            let keys = Object.keys(jqxhr.responseJSON.errors);
            message = jqxhr.responseJSON.errors[keys[0]][0];
        } else if (typeof jqxhr.responseJSON.message !== 'undefined') {
            message = jqxhr.responseJSON.message;
        }

        if (message) {
            $('#alert').modal('show').find('.modal-body').text(message);
        }
    });

    /**
     * Restore deleted post
     */
    $('.btn-res').click(function() {
        var form = $('<form>', {'method': 'POST', 'action': $(this).attr('href')})
            .append('<input type="hidden" name="_token" value="' + $('meta[name="csrf-token"]').attr('content') + '">')
            .append('<input type="submit">'); // firefox requires submit button

        $('body').append(form);

        form.submit();

        return false;
    });

    /**
     * Rollback post to previous version
     */
    $('.btn-rollback').click(function() {
        $('#form-rollback').attr('action', $(this).attr('href'));
        $('#confirm').modal('show').find('.btn-danger').attr('data-submit-state', 'Przywracanie...');

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
        });

        return false;
    });

    $('#btn-lock a').click(function() {
        var $this = $(this);

        $.post($this.attr('href'), function() {
            $this.parent().toggleClass('on');
            $this.children('span').text($this.parent().hasClass('on') ? 'Odblokuj wątek' : 'Zablokuj wątek');
        });

        return false;
    });

    /**
     * Move to another category
     */
    $('#btn-move ul a[href]').click(function() {
        $('#modal-move').modal('show').find(':hidden[name="slug"]').val($(this).data('slug'));

        return false;
    });

    /**
     * Edit topic subject
     */
    $('#btn-edit-subject a').click(function() {
        $('#modal-subject').modal('show').find('input').inputFocus();

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
    $('.ico.new').click(function() {
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
        $('#modal-post-delete').modal('show').parent().attr('action', $(this).attr('href'));

        return false;
    });

    /**
     * Share post link
     */
    $('.btn-share').one('click', function() {
        let url = $(this).attr('href');
        let $input = $('<input type="text" class="form-control input-sm" style="width: 300px" value="' + url + '" readonly />');

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
     * Merge with previous
     */
    $('.btn-merge').click(function() {
        $('#modal-merge').modal('show').parent().attr('action', $(this).attr('href'));

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
        preventDuplicate(() => {
            $.post($(this).attr('href'), json => {
                $(this).toggleClass('on');
                $(this).prev().text(json.count);
            });
        });

        return false;
    });

    $('.vote-accept[href]').click(function() {
        preventDuplicate(() => {
            $.post($(this).attr('href'), () => {
                $(this).toggleClass('on');
                $('.vote-accept').not(this).removeClass('on');
            });
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
        window.location.href = $(this).data('url') + '/' + $(this).val();
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
                $('textarea', $form).val('').keyup();
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
        });

        return false;
    })
    .on('click', '.btn-comment-del', function() {
        var $this = $(this);

        $('#modal-comment-delete').modal('show').one('click', '.danger', function() {
            $(this).disable();

            $.post($this.attr('href'), () => {
                $(this).enable();
                $('#modal-comment-delete').modal('hide');

                $this.parent().remove();
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
        let $comment = $(this).parent();
        let id = $comment.data('comment-id');

        $.get($(this).attr('href'), function(html) {
            comments[id] = $comment.html();
            $comment.html(html).find('textarea').prompt().fastSubmit().autogrow().inputFocus().escape(function() {
                $comment.html(comments[id]);
            });
        });

        return false;
    })
    .on('click', '.btn-reset', function() {
        let $comment = $(this).parent().parent();

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
        let $this = $(this);
        let $post = $('.post-content[data-post-id="' + $this.data('post-id') + '"]');

        if (!$this.hasClass('active')) {
            $.get($this.attr('href'), function(html) {
                let id = $this.data('post-id');

                posts[id] = $post.html();
                $post.html(html).find('textarea').prompt().fastSubmit().autogrow().inputFocus().escape(function() {
                    $post.html(posts[id]);
                    $this.removeClass('active');
                });

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

        $('button[type=submit]', $form).attr('disabled', 'disabled').text('Zapisywanie...');
        $('textarea', $form).attr('readonly', 'readonly');

        $.post($form.attr('action'), $form.serialize(), function(html) {
            $post.html(html);
            $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');

            Prism.highlightAll();
        })
        .always(function() {
            $('button[type=submit]', $form).removeAttr('disabled').text('Zapisz');
            $('textarea', $form).removeAttr('readonly');
        });

        return false;
    })
    .on('click', '.btn-reset', function() {
        var $post = $(this).parent().parent();

        $post.html(posts[$post.data('post-id')]);
        $('.btn-fast-edit[data-post-id="' + $post.data('post-id') + '"]').removeClass('active');

        return false;
    });

    /**
     * Post poll's votes
     */
    $('#poll-form').submit(function() {
        $.post($(this).attr('action'), $(this).serialize(), function(html) {
            $('.box-poll').replaceWith(html);
        });

        return false;
    }).on('change', ':radio, :checkbox', function() {
        var submit = $('#poll-form').find(':submit');
        var items = $('input[name^="items"]:checked').length;

        if (items > 0 && items <= parseInt($('input[name="max_items"]').val())) {
            submit.removeAttr('disabled');
        } else {
            submit.attr('disabled', 'disabled');
        }
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
            url: _config.uploadUrl,
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

        var file = $(this).data('url');
        var suffix = file.split('.').pop().toLowerCase();
        var markdown = '';

        if (suffix === 'png' || suffix === 'jpg' || suffix === 'jpeg' || suffix === 'gif') {
            markdown = '![' + $(this).text() + '](' + $(this).data('url') + ')';
        } else {
            markdown = '[' + $(this).text() + '](' + $(this).data('url') + ')';
        }

        $('textarea[name="text"]', $form).insertAtCaret("", "", markdown);
        $('.nav-tabs a:first').tab('show');
    });

    var $form = $('#submit-form');

    if ($form.length) {
        $form
            .find('textarea[name="text"]')
            .pasteImage(function (textarea, html) {
                $('#attachments .text-center').remove();
                $('#attachments tbody').append(html);

                var link = $('a', html);
                textarea.insertAtCaret("", "", '![' + link.text() + '](' + link.data('url') + ')');
            })
            .wikiEditor()
            .prompt()
            .fastSubmit()
            .autogrow();

        $form.draft();
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

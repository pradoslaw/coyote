import declination from '../components/declination';
import Dialog from '../libs/dialog';
import 'jquery-color-animation/jquery.animate-colors';

$(function () {
    'use strict';

    $(document).ajaxError(function(event, jqxhr) {
        var error;

        if (typeof jqxhr.responseJSON.errors !== 'undefined') {
            let keys = Object.keys(jqxhr.responseJSON.errors);
            error = jqxhr.responseJSON.errors[keys[0]][0];
        } else if (typeof jqxhr.responseJSON.message !== 'undefined') {
            error = jqxhr.responseJSON.message;
        }

        if (error) {
            Dialog.alert({message: error}).show();
        }
    });

    // zawartosc tresci wpisow
    // dane do tego obiektu zapisywane sa w momencie klikniecia przycisku "Edytuj"
    var entries = {};
    // id komentarzy, ktore poddawane sa edycji
    var comments = [];
    var timeoutId;

    var Thumbs =
    {
        click: function () {
            var count = parseInt($(this).data('count'));
            var $this = $(this);

            if ($this.attr('disabled') === 'disabled') {
                return false;
            }
            $this.attr('disabled', 'disabled').text('Proszę czekać...');

            $.post($this.attr('href'), function (data) {
                count = parseInt(data.count);
                $this.data('count', count);

                if (!$this.hasClass('thumbs-on')) {
                    $this.next('.btn-subscribe:not(.subscribe-on)').click(); // po doceneniu wpisu automatycznie go obserwujemy
                }

                $this.toggleClass('thumbs-on').tooltip('destroy').removeAttr('data-original-title');
            })
            .complete(function () {
                $this.removeAttr('disabled');
                $this.text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']));

                // jezeli wpis jest w sekcji "popularne wpisy" to tam tez nalezy oznaczyc, ze
                // wpis jest "lubiany"
                $('a[href="' + $this.attr('href') + '"]').not($this).toggleClass('thumbs-on', $this.hasClass('thumbs-on')).text($this.text());
            });

            return false;
        },
        enter: function () {
            var count = parseInt($(this).data('count'));
            var $this = $(this);

            if (count === 0 || typeof $this.attr('data-original-title') !== 'undefined') {
                return;
            }

            timeoutId = setTimeout(function() {
                $.get($this.attr('href'), function(html) {
                    $this.attr('title', html);

                    if (html.length) {
                        var count = html.split("\n").length;

                        $this.attr('title', html.replace(/\n/g, '<br />'))
                            .data('count', count)
                            .text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']))
                            .tooltip({html: true, trigger: 'hover'})
                            .tooltip('show');
                    }
                });

            }, 500);
        },
        leave: function () {
            clearTimeout(timeoutId);
        }
    };

    $('#microblog')
        .on('click', '.btn-reply', function () {
            var login = $(this).data('login');
            var input = $(this).parent().next('.microblog-comments').find('.comment-form textarea');

            if (login.indexOf(' ') > -1 || login.indexOf('.') > -1) {
                login = '{' + login + '}';
            }

            input.val(input.val() + '@' + login + ': ').focus();
        })
        .on('click', '.btn-subscribe', function () {
            var $this = $(this);

            if ($this.attr('disabled') === 'disabled') {
                return false;
            }

            $this.attr({disabled: 'disabled', 'data-label': $(this).text()}).text('Proszę czekać...');

            $.post($this.attr('href'), function () {
                $this.toggleClass('subscribe-on').text($this.data('label')).removeAttr('disabled');
            });

            return false;
        })
        .on('click', '.btn-thumbs, .btn-sm-thumbs', Thumbs.click)
        .on('mouseenter', '.btn-thumbs, .btn-sm-thumbs', Thumbs.enter)
        .on('mouseleave', '.btn-thumbs, .btn-sm-thumbs', Thumbs.leave)
        .on('click', '.btn-edit', function (e) {
            var $this = $(this);
            var entryText = $('#entry-' + $this.data('id')).find('.microblog-text');

            if (typeof entries[$this.data('id')] === 'undefined') {
                $.get($this.attr('href'), function (html) {
                    entries[$this.data('id')] = entryText.html();
                    entryText.html(html);

                    var $form = initForm($('.microblog-submit', entryText));

                    $form.unbind('submit').submit(function() {
                        var data = $form.serialize();
                        $(':input', $form).attr('disabled', 'disabled');

                        $.post($form.attr('action'), data, function(html) {
                            entryText.html(html);
                            delete entries[$this.data('id')];
                        })
                        .always(function() {
                            $(':input', $form).removeAttr('disabled');
                        });

                        return false;
                    }).escape(function() {
                        entryText.html(entries[$this.data('id')]);
                        delete entries[$this.data('id')];
                    });
                });
            } else {
                entryText.html(entries[$this.data('id')]);
                delete entries[$this.data('id')];
            }

            e.preventDefault();
        })
        .on('click', '.btn-remove', function () {
            let $this = $(this);
            let dialog = Dialog.confirm({message: 'Czy na pewno usunąć ten wpis?'});

            dialog.getButton('Tak, usuń').onClick = (e) => {
                $(e.currentTarget).disable();

                $.post($this.attr('href'), function() {
                    dialog.close();

                    $('#entry-' + $this.data('id')).fadeOut(500);
                });

                return false;
            };

            dialog.build().show();

            return false;
        })
        .on('focus', '.comment-form textarea', function() {
            if (typeof $(this).data('prompt') === 'undefined') {
                $(this).prompt().fastSubmit().autogrow().data('prompt', 'yes');
            }
        })
        .on('submit', '.comment-form', function() {
            var $form = $(this);
            var $input = $('textarea', $form);
            var data = $form.serialize();

            $input.attr('disabled', 'disabled');

            $.post($form.attr('action'), data, function(json) {
                $(json.html).insertBefore($form);
                $input.val('').keyup();

                if (json.subscribe) {
                    $('#entry-' + $('input[name="parent_id"]', $form).val()).find('.btn-subscribe').addClass('subscribe-on');
                }
            })
            .always(function() {
                $input.removeAttr('disabled');
            });

            return false;
        })
        .on('click', '.btn-sm-edit', function(e) {
            var id = $(this).data('id');
            var comment = $('#comment-' + id);

            var form = comment.find('.write-content');
            var body = comment.find('.comment-body');

            form.toggle();
            body.toggle();

            if (!form.hasClass('js-editable')) {
                form.addClass('js-editable');

                var textarea = $('textarea', form);
                textarea.autogrow().inputFocus().prompt().escape(function() {
                    body.show();
                    form.hide();
                });

                form.fastSubmit().submit(function() {
                    var data = form.serialize();

                    textarea.attr('disabled', 'disabled');

                    $.post($(this).attr('action'), data, function(json) {
                        comment.replaceWith(json.html);
                    })
                    .always(function() {
                        textarea.removeAttr('disabled');
                    });

                    return false;
                });
            }
        })
        .on('click', '.btn-sm-remove', function() {
            let $this = $(this);
            let dialog = Dialog.confirm({message: 'Czy na pewno usunąć ten wpis?'});

            dialog.getButton('Tak, usuń').onClick = (e) => {
                $(e.currentTarget).disable();

                $.post($this.attr('href'), function() {
                    dialog.close();

                    $('#comment-' + $this.data('id')).fadeOut(500);
                });

                return false;
            };

            dialog.build().show();

            return false;
        })
        .on('click', '.show-all a', function() {
            var $this = $(this);
            $this.text('Proszę czekać...');

            $.get($this.attr('href'), function(html) {
                $this.parent().parent().replaceWith(html);
            });

            return false;
        })
        .on('click', 'a[data-toggle="lightbox"]', function(e) {
            e.preventDefault();

            require.ensure([], (require) => {
                require('ekko-lightbox/dist/ekko-lightbox');

                $(this).ekkoLightbox({
                  left_arrow_class: '.fa .fa-angle-left .ekko-lightbox-prev',
                  right_arrow_class: '.fa .fa-angle-right .ekko-lightbox-next',
                });
            });
        })
        .on('click', '.read-more', function()
        {
            $(this).prev().removeAttr('style').children('.microblog-gradient').remove();
            $(this).remove();
        });

    function initForm($form) {

        var removeThumbnail = function () {
            $(this).parent().parent().remove();
        };

        function add(data) {
            var thumbnail = $('.thumbnail:last', $form);

            $('.spinner', thumbnail).remove();
            $('img', thumbnail).attr('src', data.url);

            $('<div>', {'class': 'btn-flush'}).html('<i class="fa fa-remove fa-2x"></i>').click(removeThumbnail).appendTo(thumbnail);
            $('<input type="hidden" name="thumbnail[]" value="' + data.name + '">').appendTo(thumbnail);
        }

        if (jQuery.fn.pasteImage) {
            $('textarea', $form).pasteImage(function (textarea, result) {
                    $.get(uploadUrl, function (tmpl) {
                        $('.thumbnails', $form).append(tmpl);
                        add(result);
                    });
                })
                .prompt()
                .fastSubmit()
                .autogrow()
                .inputFocus();
        }

        $form.on('click', '.btn-flush', removeThumbnail)
            .submit(function () {
                var data = $form.serialize();
                $(':input', $form).attr('disabled', 'disabled');

                $.post($form.attr('action'), data, function(html) {
                    $(html).hide().insertAfter('nav.text-center').fadeIn(900);
                    $('textarea', $form).val('').trigger('keydown');
                    $('.thumbnails', $form).html('');
                })
                    .always(function() {
                        $(':input', $form).removeAttr('disabled');
                    });

                return false;
            })
            .on('click', '.btn-cancel', function () {
                var id = parseInt($(this).data('id'));
                $('#entry-' + id).find('.microblog-text').html(entries[id]);

                delete entries[id];
                return false;
            })
            .delegate('#btn-upload', 'click', function () {
                $('.input-file', $form).click();
            })
            .delegate('.input-file', 'change', function () {
                var file = this.files[0];

                if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' && file.type !== 'image/jpeg') {
                    Dialog.alert({message: 'Format pliku jest nieprawidłowy. Załącznik musi być zdjęciem JPG, PNG lub GIF'}).show();
                }
                else {
                    var formData = new FormData($form[0]);

                    $.get(uploadUrl, function(tmpl) {
                        $('.thumbnails', $form).append(tmpl);

                        $.ajax({
                            url: uploadUrl,
                            type: 'POST',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                add(data);
                            },
                            error: function (err) {
                                if (typeof err.responseJSON !== 'undefined') {
                                    Dialog.alert({message: err.responseJSON.photo[0]}).show();
                                }

                                $('.thumbnail:last', $form).remove();
                            }
                        }, 'json');
                    });
                }
            });

        return $form;
    }

    initForm($('.microblog-submit'));

    $(window).load(function() {
        $('.microblog-wrapper').each(function() {
            if ($(this).height() > 305) {
                // aby zadzialal max-height, nalezy ustawic display: block. domyslnie natomiast microblog-texts
                // posiada selektor: display: table, aby zadzialalo zawieranie dlugich linii tekstu
                $(this).css({'max-height': '300px', display: 'block', overflow: "hidden", position: "relative"}).append('<div class="microblog-gradient"></div>');
                $('<a class="read-more" href="javascript:">Zobacz całość</a>').insertAfter(this);
            }
        });
    });

    if ('onhashchange' in window) {
        var onHashChange = function () {
            var hash = window.location.hash;

            if (hash.substring(1, 6) === 'entry' || hash.substring(1, 8) === 'comment') {
                var object = $(hash);
                var panel = object.find('.panel');

                if (panel.length) {
                    object = panel;
                }

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

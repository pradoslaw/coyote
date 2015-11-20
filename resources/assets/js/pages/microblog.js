$(function () {
    'use strict';

    var IMG_PLACEHOLDER = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAADIAQMAAAAk14GrAAAABlBMVEXd3d3///+uIkqAAAAAI0lEQVRoge3BMQEAAADCoPVPbQ0PoAAAAAAAAAAAAAAAAHg2MgAAAYXziG4AAAAASUVORK5CYII=';

    function tmpl(id, data) {
        var str = $(id).clone().html();

        for (var item in data) {
            str = str.replace('[[' + item + ']]', data[item]);
        }

        return str;
    }

    // zawartosc tresci wpisow
    // dane do tego obiektu zapisywane sa w momencie klikniecia przycisku "Edytuj"
    var content = {};
    var timeoutId;

    var Thumbs =
    {
        click: function () {
            var count = parseInt($(this).data('count'));
            var $this = $(this);

            $this.addClass('loader').text('Proszę czekać...');

            $.post($this.attr('href'), function (data) {
                count = parseInt(data.count);
                $this.toggleClass('thumbs-on');
                $this.data('count', count);
            })
                .complete(function () {
                    $this.removeClass('loader');
                    $this.text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']));
                })
                .fail(function (err) {
                    $('#alert').modal('show');
                    $('.modal-body').text(err.responseJSON.error);
                });

            return false;
        },
        enter: function () {
            var count = parseInt($(this).data('count'));

            if (count > 0) {
                var $this = $(this);

                if (typeof $this.attr('title') === 'undefined') {
                    timeoutId = setTimeout(function () {
                        $.get($this.attr('href'), function (html) {
                            $this.attr('title', html);

                            if (html.length) {
                                var count = html.split("\n").length;

                                $this.attr('title', html.replace(/\n/g, '<br />'))
                                    .data('count', count)
                                    .text(count + ' ' + declination(count, ['głos', 'głosy', 'głosów']))
                                    .tooltip({html: true})
                                    .tooltip('show');
                            }
                        });

                    }, 500);
                }
            }

            $(this).off('mouseenter');
        },
        leave: function () {
            clearTimeout(timeoutId);
            $('.tooltip').remove();
        }
    };

    $('#microblog').on('click', '.btn-reply', function () {
        $(this).parent().next('.microblog-comments').find('input').focus();
    })
        .on('click', '.btn-watch', function () {
            var $this = $(this);

            $.post($this.attr('href'), function () {
                $this.toggleClass('watch-on');
            })
                .fail(function (err) {
                    $('#alert').modal('show');
                    $('.modal-body').text(err.responseJSON.error);
                });

            return false;
        })
        .on('click', '.btn-thumbs, .btn-sm-thumbs', Thumbs.click)
        .on('mouseenter', '.btn-thumbs, .btn-sm-thumbs', Thumbs.enter)
        .on('mouseleave', '.btn-thumbs, .btn-sm-thumbs', Thumbs.leave)
        .on('click', '.btn-edit', function (e) {
            var $this = $(this);

            $.get($this.attr('href'), function (html) {
                var entryText = $('#entry-' + $this.data('id')).find('.microblog-text');

                content[$this.data('id')] = entryText.html();
                entryText.html(html);

                var $form = initForm($('.microblog-submit', entryText));

                $form.unbind('submit').submit(function() {
                    $.post($form.attr('action'), $form.serialize(), function (html) {
                        entryText.html(html);
                        delete content[id];
                    })
                        .fail(function (err) {
                            $('#alert').modal('show');
                            $('.modal-body').text(err.responseJSON.error);
                        });

                    return false;

                });
            });

            e.preventDefault();
        })
        .on('click', '.btn-remove', function () {
            var $this = $(this);

            $.post($this.attr('href'), function() {
                $('#entry-' + $this.data('id')).fadeOut(1000);
            })
                .fail(function(err) {
                    $('#alert').modal('show');
                    $('.modal-body').text(err.responseJSON.error);
                });

            return false;
        });

    function initForm($form) {

        var onThumbnailClick = function () {
            $(this).parent().parent().remove();
        };

        $form.on('click', '.btn-flush', onThumbnailClick)
            .submit(function () {
                $.post($form.attr('action'), $form.serialize(), function (html) {
                        $(html).hide().insertAfter('nav.text-center').fadeIn(900);
                })
                    .fail(function (err) {
                        $('#alert').modal('show');
                        $('.modal-body').text(err.responseJSON.error);
                    });

                return false;
            })
            .on('click', '.btn-cancel', function () {
                var id = parseInt($(this).data('id'));
                $('#entry-' + id).find('.microblog-text').html(content[id]);

                delete content[id];
                return false;
            })
            .delegate('#btn-upload', 'click', function () {
                $('.input-file', $form).click();
            })
            .delegate('.input-file', 'change', function () {
                var file = this.files[0];

                if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/gif' && file.type !== 'image/jpeg') {
                    $('#alert').modal('show');
                    $('.modal-body').text('Format pliku jest nieprawidłowy. Załącznik musi być zdjęciem JPG, PNG lub GIF');
                }
                else {
                    var formData = new FormData($form[0]);

                    $.ajax({
                        url: uploadUrl,
                        type: 'POST',
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        beforeSend: function () {
                            $('.thumbnails', $form).append(tmpl('#tmpl-thumbnail', {
                                'src': IMG_PLACEHOLDER,
                                'class': 'spinner',
                                'fa': 'fa fa-spinner fa-spin fa-2x'
                            }));
                        },
                        success: function (data) {
                            var thumbnail = $('.thumbnail:last', $form);

                            $('.spinner', thumbnail).remove();
                            $('img', thumbnail).attr('src', data.url);

                            $('<div>', {'class': 'btn-flush'}).html('<i class="fa fa-remove fa-2x"></i>').click(onThumbnailClick).appendTo(thumbnail);
                            $('<input type="hidden" name="thumbnail[]" value="' + data.name + '">').appendTo(thumbnail);
                        },
                        error: function (err) {
                            $('#alert').modal('show');

                            if (typeof err.responseJSON !== 'undefined') {
                                $('.modal-body').text(err.responseJSON.photo[0]);
                            }

                            $('.thumbnail:last', $form).remove();
                        }
                    }, 'json');
                }
            });

        return $form;
    }

    initForm($('.microblog-submit'));

});
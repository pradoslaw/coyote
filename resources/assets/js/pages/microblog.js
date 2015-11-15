$(function() {
    'use strict';

    var IMG_PLACEHOLDER = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAfQAAADIAQMAAAAk14GrAAAABlBMVEXd3d3///+uIkqAAAAAI0lEQVRoge3BMQEAAADCoPVPbQ0PoAAAAAAAAAAAAAAAAHg2MgAAAYXziG4AAAAASUVORK5CYII=';

    function tmpl(id, data) {
        var str = $(id).clone().html();

        for (var item in data) {
            str = str.replace('[[' + item + ']]', data[item]);
        }

        return str;
    }

    function initForm($form) {

        var onThumbnailClick = function() {
            $(this).parent().remove();
        };

        $form.submit(function() {

        })
        .delegate('#btn-upload', 'click', function() {
            $('.input-file', $form).click();
        })
        .delegate('.input-file', 'change', function() {
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
                    beforeSend: function() {
                        $('.thumbnails').append(tmpl('#tmpl-thumbnail', {'src': IMG_PLACEHOLDER, 'class': 'spinner', 'fa': 'fa fa-spinner fa-spin fa-2x'}));
                    },
                    success: function(data) {
                        var thumbnail = $('.thumbnail:last');

                        $('.spinner', thumbnail).remove();
                        $('img', thumbnail).attr('src', data.url);

                        $('<div>', {'class': 'btn-delete'}).html('<i class="fa fa-remove fa-2x"></i>').click(onThumbnailClick).appendTo(thumbnail);
                    },
                    error: function(err) {
                        $('#alert').modal('show');

                        if (typeof err.responseJSON !== 'undefined') {
                            $('.modal-body').text(err.responseJSON.photo[0]);
                        }

                        $('.thumbnail:last').remove();
                    }
                }, 'json');
            }
        });
    }

    initForm($('.microblog-submit'));

});
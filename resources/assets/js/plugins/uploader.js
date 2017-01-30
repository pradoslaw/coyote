import Dialog from '../libs/dialog';

$(function() {
    'use strict';

    $.uploader = function(options) {
        let defaults = {
            input: 'photo',
            changeButton: $('#btn-change-photo'),
            deleteButton: $('#btn-delete-photo'),
            onChanged: function(data) {},
            onDeleted: function() {}
        };

        let setup = $.extend(defaults, options);

        let form = $('<form />', {method: 'post', 'action': setup.changeButton.attr('href')});
        let input = $('<input />', {type: 'file', id: 'input-file', name: setup.input, style: 'visibility: hidden; height: 0'}).appendTo(form);

        form.appendTo('body');

        input.change(function() {
            let formData = new FormData(form[0]);

            $.ajax({
                url: form.attr('action'),
                type: 'POST',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    setup.changeButton.attr('disabled', 'disabled');
                },
                success: function (data) {
                    $('.img-container > img').attr('src', data.url);

                    $('.img-container').show();
                    $('.img-placeholder').hide();

                    setup.onChanged(data);
                },
                complete: function() {
                    setup.changeButton.removeAttr('disabled');
                },
                error: function (err) {
                    let keys = Object.keys(err.responseJSON);

                    Dialog.alert({message: err.responseJSON[keys[0]][0]}).show();
                }
            }, 'json');
        });

        setup.changeButton.on('click', function() {
            input.click();

            return false;
        });

        setup.deleteButton.on('click', function() {
            if (typeof $(this).attr('href') != 'undefined') {
                $.post($(this).attr('href'));
            }

            $('.img-container').hide();
            $('.img-placeholder').show();

            setup.onChanged({url: $('.img-placeholder > img').attr('src')});
            setup.onDeleted();

            return false;
        });
    };
});

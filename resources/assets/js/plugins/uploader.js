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
                    let errors = err.responseJSON.errors;
                    let error = errors[Object.keys(errors)[0]][0];

                    Dialog.alert({message: error}).show();
                }
            }, 'json');
        });

        setup.changeButton.on('click', function() {
            input.click();

            return false;
        });

        setup.deleteButton.on('click', function() {
            let dialog = Dialog.confirm({
                message: 'Czy na pewno usunąć?',
                buttons: [{
                    label: 'Anuluj',
                    attr: {
                        'class': 'btn btn-default',
                        'type': 'button',
                        'data-dismiss': 'modal'
                    }
                },
                {
                    label: 'Tak, usuń',
                    attr: {
                        'class': 'btn btn-danger',
                        'type': 'submit',
                        'data-submit-state': 'Usuwanie...'
                    },
                    onClick: () => {
                        if (typeof $(this).attr('href') != 'undefined') {
                            $.post($(this).attr('href'));
                        }

                        $('.img-container').hide();
                        $('.img-placeholder').show();

                        setup.onChanged({url: $('.img-placeholder > img').attr('src')});
                        setup.onDeleted();

                        dialog.close();
                    }
                }]
            });

            dialog.show();

            return false;
        });
    };
});

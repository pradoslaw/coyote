import { default as tinyConfig } from '../../libs/tinymce';
import Dialog from '../../legacy/dialog';

$(function() {
    tinymce.init(tinyConfig);

    let form = $('<form />', {method: 'post', 'action': $('#uploader').data('upload-url')});
    $('<input />', {type: 'file', name: 'cv', id: 'input-file', style: 'visibility: hidden; height: 0'}).appendTo(form);

    form.appendTo('body');

    $('#uploader')
        .click(function() {
            $('#input-file').click();
        })
        .text($(':hidden[name="cv"]').val().split('_', 2)[1]);

    $('#input-file').change(function() {
        if (!$(this).val()) {
            return;
        }

        let formData = new FormData(form[0]);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: data => {
                $('#uploader').text(data.name);
                $(':hidden[name="cv"]').val(data.filename);
            },
            error: err => {
                Dialog.alert({message: err.responseJSON.cv[0]}).show();
            }
        }, 'json');
    });
});

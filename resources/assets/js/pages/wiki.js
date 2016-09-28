$(() => {
    $('#box-comment').on('submit', 'form.comment-form', e => {
        let form = $(e.currentTarget);

        $.post(form.attr('action'), form.serialize(), html => {
            let commentId = parseInt($(':hidden[name="comment_id"]', form).val());
            form.find('textarea').val('');

            if (commentId) {
                $('#comment-' + commentId).replaceWith(html);
            }
            else {
                $('#comments-container').prepend(html);
            }
        })
        .always(() => {
            form.find(':submit').removeAttr('disabled').text('Zapisz');
        })
        .error(event => {
            let modal = $('#modal-error');
            modal.modal('show');

            if (typeof event.responseJSON !== 'undefined') {
                let error = '';

                if (typeof event.responseJSON.error !== 'undefined') {
                    error = event.responseJSON.error;
                }
                else {
                    let key = Object.keys(event.responseJSON)[0];
                    error = event.responseJSON[key];
                }

                modal.find('.modal-body').text(error);
            }
        });

        return false;
    })
    .on('click', '.btn-edit', e => {
        let self = $(e.currentTarget);

        $('.comment-form, .comment-content', '#comment-' + self.data('id')).toggle();
        $('.comment-form').find('textarea').inputFocus();
    })
    .on('click', 'button.btn-cancel', e => {
        let form = $(e.currentTarget).parents('form');

        form.hide();
        form.prev('.comment-content').show();
    })
    .on('focus', 'textarea:not(.clicked)', e => {
        $(e.currentTarget).addClass('clicked').autogrow().prompt().fastSubmit();
    });
});

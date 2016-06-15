$(() => {

    $('#box-comment').on('submit', 'form.comment-edit-form', (e) => {
        let form = $(e.currentTarget);

        $.post(form.attr('action'), form.serialize(), (html) => {
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
        .error((event) => {
            let modal = $('#modal-error');
            modal.modal('show');

            if (typeof event.responseJSON !== 'undefined') {
                let key = Object.keys(event.responseJSON)[0];
                modal.find('.modal-body').text(event.responseJSON[key][0]);
            }
        });

        return false;
    })
    .on('click', '.btn-edit', (e) => {
        let self = $(e.currentTarget);

        $('.comment-edit-form, .comment-content', '#comment-' + self.data('id')).toggle();
        $('.comment-edit-form').find('textarea').inputFocus();
    })
    .on('click', 'button.btn-danger', (e) => {
        $(e.currentTarget).parents('form').hide().next('.comment-content').show();
    })
    .on('focus', 'textarea:not(.clicked)', (e) => {
        $(e.currentTarget).addClass('clicked').autogrow().prompt().fastSubmit();
    });
});
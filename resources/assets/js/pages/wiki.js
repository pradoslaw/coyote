$(() => {
    $('#box-comment').on('submit', 'form', (e) => {
        let self = $(e.currentTarget);

        $.post(self.attr('action'), self.serialize(), (html) => {
            $('#comments-container').prepend(html);
        })
        .always(() => {
            self.find(':submit').removeAttr('disabled').text('Zapisz');
        });

        return false;
    })
    .on('click', '.btn-edit', (e) => {
        let self = $(e.currentTarget);
        let commentId = self.data('id');

        $('.comment-edit-form, .comment-content', '#comment-' + commentId).toggle();
    })
        .on('click', 'button.btn-danger', (e) => {
            let self = $(e.currentTarget);

            self.parents('form').next('.comment-content').show();
            self.parents('form').hide();
        });
});
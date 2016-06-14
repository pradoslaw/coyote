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
    .on('click', '.btn-edit', () => {
        let self = $(e.currentTarget);
        let commentId = self.data('id');

        $('#comment-' + commentId).find('.comment-edit-form, .comment-content').toggle();
    });
});
$(() => {
    'use strict';

    $('.btn-subscribe').click((e) => {
        let self = $(e.currentTarget);

        $.post(self.attr('href'), () => {
            self.toggleClass('on');
            self.find('span').text(self.data(self.hasClass('on') ? 'off' : 'on'));
        }).fail((e) => {
            $('#modal-unauthorized').modal('show');
        });

        return false;
    });
});
$(() => {
    'use strict';

    $('.btn-subscribe').click((e) => {
        let self = $(e.currentTarget);

        self.toggleClass('on');
        self.find('span').text(self.data(self.hasClass('on') ? 'off' : 'on'));

        $.post(self.attr('href')).fail((e) => {
            $('#modal-unauthorized').modal('show');
        });

        return false;
    });
});
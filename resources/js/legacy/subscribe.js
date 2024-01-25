$(() => {
  'use strict';
  $('.btn-subscribe').click(event => {
    const self = $(event.currentTarget);
    if (!self.data('href')) {
      return;
    }
    $
      .post(self.data('href'), () => {
        self.toggleClass('on');
        self.find('span').text(self.data(self.hasClass('on') ? 'off' : 'on'));
      })
      .fail((e) => {
        $('#modal-unauthorized').modal('show');
      });
    return false;
  });
});

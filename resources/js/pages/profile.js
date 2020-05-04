import Config from '../libs/config';
import PerfectScrollbar from 'perfect-scrollbar';

$(function () {
  const container = document.getElementById('wrap');

  if (!container) {
    return;
  }

  new PerfectScrollbar(container);

  let pending = false;

  container.addEventListener('ps-y-reach-end', function () {
    if (pending) {
      return;
    }

    let offset = $('#reputation').find('.reputation-item').length;
    pending = true;

    $.get(Config.get('reputation_url'), {offset: offset}, function (html) {
      $('#reputation').append(html);

      pending = false;
    });
  });
});

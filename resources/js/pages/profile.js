import PerfectScrollbar from 'perfect-scrollbar';
import axios from 'axios';

(function () {
  const container = document.getElementById('wrap');

  if (!container) {
    return;
  }

  new PerfectScrollbar(container);

  let isPending = false;

  container.addEventListener('ps-y-reach-end', function () {
    if (isPending) {
      return;
    }

    const el = document.getElementById('reputation');
    const offset = el.childNodes.length;

    isPending = true;

    axios.get(`/Profile/${window.userId}/History`, {params: {offset: offset}}).then(data => {
      el.insertAdjacentHTML('beforeend', data.data);

      isPending = false;
    });
  });
})();

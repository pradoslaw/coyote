import axios from 'axios';

function openDialog(event) {
  const el = event.currentTarget;

  axios.get('/Flag', {params: { url: el.dataset.url, metadata: el.dataset.metadata }}).then(result => {
    const html = result.data;
    // @todo usuniecie jquery
    $(html).appendTo('body');

    $('#flag').find('.modal').modal('show');
  });

  return false;
}

function bindEvents() {
  const links = document.querySelectorAll('a[data-metadata]');

  links.forEach(link => {
    link.addEventListener('click', openDialog);
  });
}

const observer = new MutationObserver(bindEvents);
observer.observe(document.body, { attributes: true, childList: true, subtree: true });

import axios from 'axios';
import VueFlagModal from "../components/flags/modal.vue";
import {createVueAppGhost} from "../vue";

function openModal(event) {
  const el = event.currentTarget;
  axios.get('/Flag').then(result => {
    const [app, domElement] = createVueAppGhost(VueFlagModal, {
      url: el.dataset.url,
      metadata: el.dataset.metadata,
      types: result.data,
      onClose: () => {
        app.$destroy();
        document.body.removeChild(domElement);
      },
    });
    document.body.append(domElement);
  });

  return false;
}

function bindEvents() {
  const links = document.querySelectorAll('a[data-metadata]');

  links.forEach(link => link.addEventListener('click', openModal));
}

new MutationObserver(bindEvents).observe(document.body, {attributes: true, childList: true, subtree: true});

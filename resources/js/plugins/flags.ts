import axios from 'axios';
import VueFlagModal from "../components/flags/modal.vue";

function openModal(event) {
  const el = event.currentTarget;

  axios.get('/Flag').then(result => {
    const propsData = { url: el.dataset.url, metadata: el.dataset.metadata, types: result.data };
    const wrapper = new VueFlagModal({ propsData }).$mount();

    document.body.append(wrapper.$el);
  });

  return false;
}

function bindEvents() {
  const links = document.querySelectorAll('a[data-metadata]');

  links.forEach(link => link.addEventListener('click', openModal));
}

new MutationObserver(bindEvents).observe(document.body, { attributes: true, childList: true, subtree: true });

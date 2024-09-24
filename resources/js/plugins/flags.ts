import axios from 'axios';
import Vue from "vue";
import VueFlagModal from "../components/flags/modal.vue";

function openModal(event) {
  const el = event.currentTarget;

  axios.get('/Flag').then(result => {
    const propsData = {url: el.dataset.url, metadata: el.dataset.metadata, types: result.data};
    const flagModal = new (Vue.extend(VueFlagModal))({propsData});
    flagModal.$on('close', () => {
      flagModal.$destroy();
      document.body.removeChild(domElement);
    });
    const wrapper = flagModal.$mount();
    let domElement = wrapper.$el;
    document.body.append(domElement);
  });

  return false;
}

function bindEvents() {
  const links = document.querySelectorAll('a[data-metadata]');

  links.forEach(link => link.addEventListener('click', openModal));
}

new MutationObserver(bindEvents).observe(document.body, {attributes: true, childList: true, subtree: true});

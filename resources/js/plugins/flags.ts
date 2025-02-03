import axios from 'axios';
import VueFlagModal from "../components/flags/modal.vue";
import {createVueAppGhost} from "../vue";

export function openFlagModal(flagUrl: string, flagMetadata: string): void {
  axios.get('/Flag').then(result => {
    const [app, domElement] = createVueAppGhost(VueFlagModal, {
      url: flagUrl,
      metadata: flagMetadata,
      types: result.data,
      onClose(): void {
        app.unmount();
        document.body.removeChild(domElement);
      },
    });
    document.body.append(domElement);
  });
}

new MutationObserver(bindEvents).observe(document.body, {attributes: true, childList: true, subtree: true});

function bindEvents(): void {
  document
    .querySelectorAll('*[data-metadata]')
    .forEach(link => link.addEventListener('click', clickHandler));
}

function clickHandler(event: Event): boolean {
  const el = event.currentTarget as HTMLElement;
  openFlagModal(el.dataset.url!, el.dataset.metadata!);
  return false;
}

import {copyToClipboardMultiline} from '../../js/plugins/clipboard';
import {notify} from "../../js/toast";

window.addEventListener('load', () => {
  document.querySelectorAll('.markdown-code .copy-button').forEach(copyButton => {
    copyButton.addEventListener('click', function (event: Event): void {
      const copyButton = event.target as HTMLElement;
      const markdownCode = copyButton.parentElement!;
      const codeSource = markdownCode.querySelector('pre code')!.textContent!;
      copyToClipboardMultiline(codeSource);
      notify({type: 'success', text: 'Kod znajduje się w schowku!'});
    });
  });
});

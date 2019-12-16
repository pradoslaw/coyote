import axios from 'axios';
import Textarea from "../libs/textarea";

export default {
  install(Vue) {
    let url;
    let input;

    const upload = (base64) => {
      input.readonly = 'readonly';

      const overlay = document.createElement('div');
      const rect = input.getBoundingClientRect();

      overlay.id = 'ajax-loader';
      overlay.innerHTML = '<i class="fa fa-cog fa-spin"></i>';
      overlay.style.left = `${rect.left}px`;
      overlay.style.top = `${rect.top + document.documentElement.scrollTop}px`;
      overlay.style.width = `${rect.width}px`;
      overlay.style.height = `${rect.height}px`;

      document.body.append(overlay);

      axios.post(url, base64, {'Content-Type': 'application/x-www-form-urlencoded'})
        .then(response => {
          const textarea = new Textarea(input);

          textarea.insertAtCaret('', '', '![' + response.data.name + '](' + response.data.url + ')');
        })
        .finally(() => {
          input.removeAttribute('readonly');

          overlay.remove();
        });
    };

    const handler = e => {
      let items = [];

      if (e.clipboardData && e.clipboardData.items) {
        items = e.clipboardData.items;
      }

      if (items.length) {
        let blob = items[0].getAsFile();
        let fr = new FileReader();

        fr.onload = function (e) {
          let mime = /^data:image/g;

          if (!mime.test(e.target.result)) {
            return false;
          }

          upload(e.target.result);
        };

        if (blob) {
          fr.readAsDataURL(blob);
        }
      }
    };

    Vue.directive('clipboard', {
      bind(el, binding) {
        input = el;
        url = binding.value;

        el.addEventListener('paste', handler);
      },

      unbind(el) {
        el.removeEventListener('paste', handler);
      }
    });
  }
};

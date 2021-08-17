import axios from 'axios';

export default {
  install(Vue, options) {
    if (!options.url) {
      throw Error('No clipboard URL was provided.');
    }

    let successCallback, errorCallback;
    let textarea;

    const upload = async (base64) => {
      textarea.readonly = 'readonly';

      const overlay = document.createElement('div');
      const rect = textarea.getBoundingClientRect();

      overlay.id = 'ajax-loader';
      overlay.innerHTML = '<i class="fa fa-spinner fa-spin fa-fw"></i>';
      overlay.style.left = `${rect.left}px`;
      overlay.style.top = `${rect.top + document.documentElement.scrollTop}px`;
      overlay.style.width = `${rect.width}px`;
      overlay.style.height = `${rect.height}px`;

      document.body.append(overlay);

      const response = await fetch(base64);
      const blob = await response.blob();

      const formData = new FormData();
      formData.append('asset', blob);

      axios.post(options.url, formData)
        .then(response => successCallback(response.data))
        .finally(() => {
          textarea.removeAttribute('readonly');

          overlay.remove();
        })
        .catch(err => errorCallback(err));
    };

    const handler = e => {
      let items = (e.clipboardData || e.originalEvent.clipboardData).items;
      let blob = null;

      const fr = new FileReader();

      fr.onload = function (e) {
        upload(e.target.result);
      };

      for (const item of items) {
        if (item.type.indexOf('image') === 0) {
          blob = item.getAsFile();

          if (blob) {
            fr.readAsDataURL(blob);
          }
        }
      }
    };

    Vue.directive('paste', {
      bind(el, binding) {

        switch (binding.arg) {
          case 'success':
            successCallback = binding.value;
            textarea = el;

            el.addEventListener('paste', handler);

            break;

          case 'error':
            errorCallback = binding.value;
            break;
        }
      },

      unbind(el) {
        el.removeEventListener('paste', handler);
      }
    });
  }
};

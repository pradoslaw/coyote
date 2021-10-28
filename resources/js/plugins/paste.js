import axios from 'axios';

async function upload(textarea, currentTarget, base64) {
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

  axios.post(currentTarget.uploadUrl, formData)
    .then(response => currentTarget.successCallback(response.data))
    .finally(() => {
      textarea.removeAttribute('readonly');

      overlay.remove();
    })
    .catch(err => currentTarget.errorCallback(err));
}

function handler(event) {
  const items = (event.clipboardData || event.originalEvent.clipboardData).items;
  const currentTarget = event.currentTarget;
  const fr = new FileReader();

  let blob = null;

  fr.onload = (e) => {
    upload(this, currentTarget, e.target.result);
  };

  for (const item of items) {
    if (item.type.indexOf('image') === 0) {
      blob = item.getAsFile();
      // default browser behaviour is to paste path to the file. we prevent that.
      event.preventDefault();

      if (blob) {
        fr.readAsDataURL(blob);
      }
    }
  }
}

export default {
  install(Vue, options) {
    if (!options.url) {
      throw Error('No clipboard URL was provided.');
    }

    Vue.directive('paste', {
      bind(el, binding) {
        switch (binding.arg) {
          case 'success':
            el.addEventListener('paste', handler);
            el.successCallback = binding.value;
            el.uploadUrl = options.url;

            break;

          case 'error':
            el.errorCallback = binding.value;
            break;
        }
      },

      unbind(el) {
        el.removeEventListener('paste', handler);
      }
    });
  }
};



function copyToClipboard(message: string): boolean {
  const input = document.createElement('input');
  let success = false;

  input.value = message;
  input.style.border = 'none';
  input.style.outline = 'none';
  input.style.boxShadow = 'none';
  input.style.background = 'transparent';

  try {
    document.body.appendChild(input);

    input.select();

    success = document.execCommand('copy');
  }
  finally {
    document.body.removeChild(input);
  }

  return success;
}

export default {
  install(Vue) {
    Vue.prototype.$copy = copyToClipboard;
  }
};

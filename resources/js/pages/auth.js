
document.querySelectorAll('.btn-github, .btn-google, .btn-fb').forEach(el => {
  el.addEventListener('click', function () {
    if (this.hasAttribute('disabled')) {
      return;
    }

    this.innerText = 'Logowanie...';
    this.setAttribute('disabled', 'disabled');

    window.location.href = this.dataset.url;
  });
});

document.getElementById('js-register-form')?.insertAdjacentHTML('beforeend', '<input style="display: none" type="checkbox" name="human" value="1" checked="checked">');


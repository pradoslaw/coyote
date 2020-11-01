document.addEventListener('submit', function (event) {
  let button = event.target.querySelector('[data-submit-state]');

  if (!button) {
    return;
  }

  const text = button.dataset.submitState;

  button.innerHTML = `<i class="fa fa-spinner fa-spin fa-fw"></i> ${text}`;
  button.classList.toggle('disabled');
});

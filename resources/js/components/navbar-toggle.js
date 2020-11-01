import axios from 'axios';

// tymczasowy test: mozliwosc zmiany menu na nowe/stare
document.getElementById('js-change-menu')?.addEventListener('click', () => {
  let header = document.getElementsByClassName('navbar')[0];

  header.classList.toggle('navbar-dark');
  header.classList.toggle('bg-dark');
  header.classList.toggle('bg-light');
  header.classList.toggle('navbar-light');

  axios.post('/User/Settings/Ajax', {'dark_theme': +header.classList.contains('navbar-dark')});
});

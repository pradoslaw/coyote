import axios from 'axios';
import Vue from 'vue';
import store from "../store/index";
import GithubButton from './github-button.vue';

Array.from(document
  .querySelectorAll('a.btn-toggle-theme'))
  .forEach(button => {
    button.addEventListener('click', event => {
      event.preventDefault();
      setTheme(!document.body.classList.contains('theme-dark'));
    });
  });

Array
  .from(document.querySelectorAll('.disable-theme-notice'))
  .forEach(button => {
    button.addEventListener('click', function () {
      Array.from(document.querySelectorAll(".theme-notice")).forEach(div => {
        div.style.display = 'none';
      })
      setTheme(false);
    });
  })

function setTheme(dark) {
  if (document.body.classList.contains('theme-dark-wip')) {
    changeTheme(false);
  } else {
    changeTheme(dark);
  }
}

function changeTheme(isDark) {
  setBodyTheme(isDark);
  setBootstrapNavigationBarTheme(isDark);
  setGithubButtonTheme(isDark);
  store.commit('theme/CHANGE_THEME', isDark);
  axios.post('/User/Settings/Ajax', {'dark_theme': isDark});
}

function setBodyTheme(isDark) {
  document.body.classList.toggle('theme-dark', isDark);
  document.body.classList.toggle('theme-light', !isDark);
}

function setBootstrapNavigationBarTheme(isDark) {
  const header = document.getElementsByClassName('navbar')[0];
  header.classList.toggle('navbar-dark', isDark);
  header.classList.toggle('navbar-light', !isDark);
}

function setGithubButtonTheme(dark) {
  githubButton.theme = dark ? 'dark' : 'light';
}

const githubButton = new Vue({
  el: '#github-button',
  components: {'vue-github-button': GithubButton},
  data() {
    return {
      theme: document.body.classList.contains('theme-dark') ? 'dark' : 'light',
    }
  },
});

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
      Array.from(document.querySelectorAll('div.theme-notice'))
        .forEach((div: Element) => {
          (div as HTMLElement).style.display = 'none';
        })
      setTheme(false);
    });
  });

function setTheme(dark: boolean): void {
  if (document.body.classList.contains('theme-dark-wip')) {
    changeTheme(false);
  } else {
    changeTheme(dark);
  }
}

function changeTheme(isDark: boolean): void {
  setBodyTheme(isDark);
  setBootstrapNavigationBarTheme(isDark);
  setGithubButtonTheme(isDark);
  store.commit('theme/CHANGE_THEME', isDark);
  axios.post('/User/Settings/Ajax', {colorScheme: isDark ? 'dark' : 'light'});
}

function setBodyTheme(isDark: boolean): void {
  document.body.classList.toggle('theme-dark', isDark);
  document.body.classList.toggle('theme-light', !isDark);
}

function setBootstrapNavigationBarTheme(isDark: boolean): void {
  const header: Element = document.getElementsByClassName('navbar')[0];
  header.classList.toggle('navbar-dark', isDark);
  header.classList.toggle('navbar-light', !isDark);
}

function setGithubButtonTheme(dark: boolean): void {
  githubButton.theme = dark ? 'dark' : 'light';
}

const githubButton = new Vue({
  el: '#github-button',
  components: {'vue-github-button': GithubButton},
  template: '<vue-github-button size="large" :theme="theme"/>',
  data() {
    return {
      theme: document.body.classList.contains('theme-dark') ? 'dark' : 'light',
    }
  },
});

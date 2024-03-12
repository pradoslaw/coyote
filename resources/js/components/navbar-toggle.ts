import axios from 'axios';
import Vue from 'vue';
import store from "../store/index";
import GithubButton from './github-button.vue';

Array
  .from(document.querySelectorAll('.disable-theme-notice'))
  .forEach(button => {
    button.addEventListener('click', function () {
      Array.from(document.querySelectorAll('div.theme-notice'))
        .forEach((div: Element) => {
          (div as HTMLElement).style.display = 'none';
        })
      changeTheme(false);
    });
  });

function changeTheme(isDark: boolean): void {
  setBodyTheme(isDark);
  setBootstrapNavigationBarTheme(isDark);
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

const isDarkTheme = document.body.classList.contains('theme-dark');
const isDarkThemeWip = document.body.classList.contains('theme-dark-wip');

new Vue({
  el: '#non-alert-controls',
  components: {'vue-github-button': GithubButton},
  data() {
    return {
      wip: isDarkThemeWip,
      theme: isDarkTheme ? 'dark' : 'light',
    }
  },
  methods: {
    toggleTheme(): void {
      const dark = !isDarkThemeWip && this.theme !== 'dark';
      changeTheme(dark);
      this.theme = dark ? 'dark' : 'light';
    },
  },
  template: `
    <div :class="['d-flex', 'align-items-center', 'h-100']">
      <div class="d-none d-xl-flex mr-2">
        <vue-github-button size="large" :theme="theme"/>
      </div>
      <span class="btn btn-sm btn-toggle-theme" @click="toggleTheme" v-if="!wip">
        <i :class="theme !== 'dark' ? 'fas fa-moon' : 'fas fa-sun'"/>
      </span>
    </div>
  `,
});

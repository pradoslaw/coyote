import axios from 'axios';
import Vue, {CreateElement, VNode} from 'vue';
import VueClickAway from "vue-clickaway";

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
      setViewTheme(false);
      storeThemeSetting('light', false);
    });
  });

function storeThemeSetting(colorScheme: Theme, dark: boolean): void {
  axios.post('/User/Settings/Ajax', {
    colorScheme: colorScheme,
    lastColorScheme: dark ? 'dark' : 'light',
  });
}

function setViewTheme(isDark: boolean): void {
  setBodyTheme(isDark);
  setBootstrapNavigationBarTheme(isDark);
  store.commit('theme/CHANGE_THEME', isDark);
}

function setBodyTheme(isDark: boolean): void {
  document.querySelector('html')!.classList.toggle('theme-dark', isDark);
  document.body.classList.toggle('theme-dark', isDark);
  document.body.classList.toggle('theme-light', !isDark);
}

function setBootstrapNavigationBarTheme(isDark: boolean): void {
  const header: Element = document.getElementsByClassName('navbar')[0];
  if (header) {
    header.classList.toggle('navbar-dark', isDark);
    header.classList.toggle('navbar-light', !isDark);
  }
}

const isDarkTheme = document.body.classList.contains('theme-dark');
const isDarkThemeWip = document.body.classList.contains('theme-dark-wip');

let colorScheme = document.body.dataset.colorScheme as Theme;
if (isDarkThemeWip) {
  colorScheme = 'light';
}

const icons = {
  'dark-theme': 'fas fa-moon',
  'light-theme': 'fas fa-sun',
  'system-theme': 'fas fa-display',
};
type Icon = keyof typeof icons;

const VueIcon = Vue.component('vue-icon', {
  props: ['icon', 'className'],
  render(h: CreateElement): VNode {
    return h('i', {class: ['fa-fw', icons[this.icon], this.className]});
  },
});

function systemColorSchemeDark(): boolean {
  return window.matchMedia && window.matchMedia('(prefers-color-scheme:dark)').matches;
}

function systemThemeDarkListener(listener: (dark: boolean) => void) {
  window.matchMedia('(prefers-color-scheme:dark)')
    .addEventListener('change', (event: MediaQueryListEvent): void => {
      listener(event.matches);
    });
}

systemThemeDarkListener(function (dark: boolean): void {
  controls.systemColorSchemeDark = dark;
});

function currentTheme(): boolean {
  if (colorScheme === 'system') {
    return systemColorSchemeDark();
  }
  return colorScheme === 'dark';
}

let dark = currentTheme();
if (dark !== isDarkTheme) {
  storeThemeSetting(colorScheme, dark);
}

if (!isDarkThemeWip) {
  setViewTheme(dark);
}

type Theme = 'light' | 'dark' | 'system';

const controls = new Vue({
  el: '#non-alert-controls',
  components: {
    'vue-github-button': GithubButton,
    'vue-icon': VueIcon,
  },
  data() {
    return {
      toggleEnabled: !isDarkThemeWip,
      open: false,
      theme: colorScheme,
      systemColorSchemeDark: systemColorSchemeDark(),
      items: {
        light: {title: 'Jasny motyw', icon: 'light-theme'},
        dark: {title: 'Ciemny motyw', icon: 'dark-theme'},
        system: {title: 'Systemowy:', icon: 'system-theme'},
      },
    };
  },
  directives: {
    'away': VueClickAway.directive,
  },
  computed: {
    dark(): boolean {
      if (this.theme === 'system') {
        return this.systemColorSchemeDark;
      }
      return this.theme === 'dark';
    },
    oppositeIcon(): Icon {
      return this.dark ? 'light-theme' : 'dark-theme';
    },
    systemThemeTitle(): string {
      return this.systemColorSchemeDark ? 'ciemny' : 'jasny';
    },
  },
  watch: {
    dark(): void {
      if (this.toggleEnabled) {
        setViewTheme(this.dark);
      }
    },
  },
  methods: {
    toggleTheme(event: Event, theme: Theme): void {
      event.stopPropagation();
      this.theme = theme;
      storeThemeSetting(theme, this.dark);
    },
    toggleOpen(): void {
      this.open = !this.open;
    },
    close(): void {
      this.open = false;
    },
  },
  template: `
    <div :class="['d-flex', 'align-items-center', 'h-100']" v-away="close">
      <div class="d-none d-xl-flex mr-2">
        <vue-github-button size="large" :theme="dark ? 'dark' : 'light'"/>
      </div>
      <span :class="['position-relative', 'btn','btn-sm', 'btn-toggle-theme', {open}]" @click="toggleOpen" v-if="toggleEnabled">
        <vue-icon :icon="oppositeIcon"/>
        <div class="dropdown-menu dropdown-menu-right" style="display:block" v-show="open">
          <span v-for="(item, itemTheme, index) in items"
                :class="['dropdown-item', {active: itemTheme === theme}]"
                @click="event => toggleTheme(event, itemTheme)">
            <vue-icon :icon="item.icon" className="mr-1"/>
            {{ item.title }}
            <span v-if="index === 2" style="opacity:0.75">
              {{ systemThemeTitle }}
            </span>
          </span>
        </div>
      </span>
    </div>
  `,
});

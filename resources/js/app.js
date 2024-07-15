import '@popperjs/core'; // must be imported before bootstrap
import Prism from 'prismjs';
import Vue from 'vue';
import 'bootstrap/js/dist/tooltip.js';
import 'bootstrap/js/dist/collapse.js';
import 'bootstrap/js/dist/dropdown.js';
import 'bootstrap/js/dist/modal.js';
import 'bootstrap/js/dist/tab.js';
import './components/breadcrumb.js';
import './components/navbar-toggle.ts';
import './components/bannerCount.ts';
import './components/scrolltop.js';
import './components/vcard.js';
import './legacy/state.js';
import './libs/axios-throttle.ts';
import './libs/csrf.js'; // setup CSRF token
import Router from './libs/router.js';
import './libs/timeago.js';
import VueAutosize from './plugins/autosize.js';
import './plugins/flags.ts';
import './plugins/sociale.js';
import './sentry.ts';
import './store/index.ts';
import './gdpr.ts';

Prism.highlightAll();
Vue.use(VueAutosize);

new Router()
  .on(['/User', '/User/Skills', '/User/Relations', '/User/Tokens'], () => require('./pages/user'))
  .on(['/Register', '/Login'], () => require('./pages/auth'))
  .on(['/Adm/Mailing'], () => require('./libs/tinymce').default())
  .on(['/User/Pm/Submit', '/User/Pm/Show/*', '/User/Pm'], () => require('./pages/pm'))
  .on(['/Mikroblogi', '/Mikroblogi/*', '/Profile/*/Microblog'], () => require('./pages/microblog'))
  .on(['/Profile/*'], () => require('./pages/profile'))
  .on(['/'], () => require('./pages/homepage'))
  .on(['/Search'], () => require('./pages/search'))
  .resolve();

// must be at the end so other vue components can render
require('./plugins/popover');

if ('serviceWorker' in navigator) {
  navigator.serviceWorker
    .register('/sw.js')
    .catch(error => console.log(error));
}

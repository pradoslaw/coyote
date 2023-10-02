import '@popperjs/core'; // must be imported before bootstrap
import Prism from 'prismjs';
import Vue from 'vue';
import './bootstrap.js';
import './components/breadcrumb.js';
import './components/navbar-toggle.js';
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

Prism.highlightAll();
Vue.use(VueAutosize);

new Router()
  .on(['/User', '/User/Skills', '/User/Relations', '/User/Tokens'], () => require('./pages/user'))
  .on(['/Register', '/Login'], () => require('./pages/auth'))
  .on(['/Adm/Firewall/*'], () => {
    require.ensure(['flatpickr', 'flatpickr/dist/l10n/pl'], require => {
      require('flatpickr');
      require('../sass/vendor/_flatpickr.scss');
      $('#expire-at').flatpickr({
        allowInput: true,
        locale: require('flatpickr/dist/l10n/pl.js').pl
      });
    });
  })
  .on(['/Adm/Mailing'], () => require('./libs/tinymce').default())
  .on(['/User/Pm/Submit', '/User/Pm/Show/*', '/User/Pm'], () => require('./pages/pm'))
  .on(['/Mikroblogi', '/Mikroblogi/*', '/Profile/*/Microblog'], () => require('./pages/microblog'))
  .on(['/Profile/*'], () => require('./pages/profile'))
  .on(['/'], () => require('./pages/homepage'))
  .on(['/Search'], () => require('./pages/search'))
  .on(['/Guide', '/Guide/*'], () => require('./pages/guide'))
  .resolve();

// must be at the end so other vue components can render
require('./plugins/popover');

if ('serviceWorker' in navigator) {
  navigator.serviceWorker
    .register('/sw.js')
    .catch(error => console.log(error));
}

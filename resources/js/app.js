import './sentry';
import './libs/csrf'; // setup CSRF token
import './store';
import './components/scrolltop.js';
import './components/breadcrumb.js';
import './components/navbar-toggle.js';
import './legacy/state.js';
import './libs/timeago.js';
import './components/vcard.js';
import './plugins/flags.ts';
import './plugins/sociale.js';
import './plugins/components.ts';
import '@popperjs/core'; // must be imported before bootstrap
import './bootstrap';
import './libs/axios-throttle.ts';
import VueAutosize from './plugins/autosize';
import Router from './libs/router';
import Prism from 'prismjs';
import Vue from 'vue';

Prism.highlightAll();

Vue.use(VueAutosize);

let r = new Router();

r.on(['/User', '/User/Skills', '/User/Relations', '/User/Tokens'], () => require('./pages/user'));
r.on(['/Register', '/Login'], () => require('./pages/auth'))
.on('/Adm/Firewall/*', () => {
  require.ensure(['flatpickr', 'flatpickr/dist/l10n/pl'], require => {
    require('flatpickr');
    require('../sass/vendor/_flatpickr.scss');

    const Polish = require('flatpickr/dist/l10n/pl.js').pl;

    $('#expire-at').flatpickr({
      allowInput: true,
      locale: Polish
    });
  });
})
.on('/Adm/Mailing', () => {
  require('./libs/tinymce').default();
})
.on(['/User/Pm/Submit', '/User/Pm/Show/*', '/User/Pm'], () => require('./pages/pm'))
.on(['/Mikroblogi', '/Mikroblogi/*', '/Profile/*/Microblog'], () => require('./pages/microblog'))
.on(['/Profile/*'], () => require('./pages/profile'))
.on('/', () => require('./pages/homepage'))
.on('/Search', () => require('./pages/search'))
.on(['/Guide', '/Guide/*'], () => require('./pages/guide'));

r.resolve();

// must be at the end so other vue components can render
require('./plugins/popover');

if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('/sw.js').catch(err => console.log(err));
}

import './store';
import './components/scrolltop.js';
import './components/breadcrumb.js';
import './components/navbar-toggle.js';
import './legacy/state.js';
import './libs/timeago.js';
import './components/vcard.js';
import './plugins/flags.ts';
import './plugins/sociale.js';
import 'popper.js';
import './bootstrap';

import Config from './libs/config';
import { default as setToken } from './libs/csrf';
import Router from './libs/router';
import Prism from 'prismjs';

Prism.highlightAll();

(function () {
  'use strict';

  setToken(Config.csrfToken());

  let r = new Router();

  r.on(['/User', '/User/Skills'], () => {
    require('./pages/user');
  })
  .on('/Praca/Application/*', () => {
    require.ensure([], require => require('./pages/job/application'), 'application');
  })
  .on('/Praca/Payment/*', () => {
    require.ensure([], require => require('./pages/job/payment'), 'payment');
  })
  .on('/Praca/Oferta', () => {
    require.ensure([], require => require('./pages/job/business'), 'business');
  })
  .on(['/Praca', '/Praca/Miasto/*', '/Praca/Technologia/*', '/Praca/Zdalna', '/Praca/Firma/*'], () => {
    require('./pages/job/homepage');
  })
  .on('/Praca/\\d+\\-*', () => {
    require('./pages/job/offer');
  })
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
  .on(['/User/Pm/Submit', '/User/Pm/Show/*', '/User/Pm'], () => {
    require('./pages/pm');
  })
  .on(['/Mikroblogi', '/Mikroblogi/*', '/Profile/*/Microblog'], () => require('./pages/microblog'))
  .on(['/Profile/*'], () => require('./pages/profile'))
  .on('/', () => require('./pages/homepage'))
  .on('/Search', () => require('./pages/search'));

  r.resolve();

  // must be at the end so other vue components can render
  require('./plugins/popover');
})();

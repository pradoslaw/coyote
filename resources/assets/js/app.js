// required for IE11
import 'core-js/fn/promise';
// required for PhantomJS to run tests
import 'core-js/modules/es6.function.bind';
// JS's startsWith() -- support for IE and old Opera
import 'core-js/modules/es6.string.starts-with';
// JS's findIndex() -- support for IE and old Opera
import 'core-js/modules/es6.array.find-index';
// JS's includes -- support for old browsers
import 'core-js/modules/es7.array.includes';
// Object.assign -- support for old browsers
import 'core-js/modules/es6.object.assign';
// array forEach -- support for old browsers
import 'core-js/modules/es6.array.for-each';
// array isArray -- support for old browser
import 'core-js/modules/es6.array.is-array';
import 'core-js/modules/es6.array.from';
// vue.js Object.values
import 'core-js/modules/es7.object.values';


import './store';
// import './components/dropdown.js';
import './components/scrolltop.js';
import './components/breadcrumb.js';
import './components/navbar-toggle.js';
import './components/state.js';
import './libs/timeago.js';
import './components/vcard.js';
import './components/popover.js';
import './components/flag.js';
import './plugins/sociale.js';
import './plugins/geo-ip';
import './bootstrap';

import Config from './libs/config';
import { default as setToken } from './libs/csrf';
import Router from './libs/router';
import Prism from 'prismjs';

Prism.highlightAll();

$(function () {
  'use strict';

  setToken(Config.csrfToken());

  let r = new Router();

  r.on('/User', () => {
    require.ensure([], require => require('./pages/user'), 'user');
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
  });

  r.resolve();
});

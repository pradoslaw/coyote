// required for IE11
import 'core-js/fn/promise';
// required for PhantomJS to run tests
import 'core-js/modules/es6.function.bind';
// JS's startsWith() -- support for IE and old Opera
import 'core-js/modules/es6.string.starts-with';
// JS's findIndex() -- support for IE and old Opera
import 'core-js/modules/es6.array.find-index';

import {RealtimeFactory} from './libs/realtime.js';
import './components/dropdown.js';
import './components/scrolltop.js';
import './components/breadcrumb.js';
import './components/navbar-toggle.js';
import './components/state.js';
import './components/date.js';
import './components/vcard.js';
import './components/popover.js';
import './components/flag.js';
import './plugins/geo-ip';
import './plugins/auto-complete';
import './bootstrap';

import Config from './libs/config';
import Router from './libs/router';
import Prism from 'prismjs';

window.ws = RealtimeFactory();
// Prism.highlightAll();
//
import './components/notifications.js';
import './components/pm.js';

$(function () {
    'use strict';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': Config.csrfToken()
        }
    });

    let r = new Router();

    r.on('/User', () => {
        require.ensure([],
            require => {
                require('./pages/user');
            },
            'user'
        );
    })
    .on('/Praca/Application/*', () => {
        require.ensure([],
            require => {
                require('./pages/job/application');
            },
            'application'
        );
    })
    .on('/Praca/Payment/*', () => {
        require.ensure([],
            require => {
                require('./pages/job/payment');
            },
            'payment'
        );
    })
    .on('/Praca/Oferta', () => {
        require.ensure([],
            require => {
                require('./pages/job/business');
            },
            'business'
        );
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
    });

    r.resolve();
});

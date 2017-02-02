// required for IE11
import 'core-js/fn/promise';
// required for PhantomJS to run tests
import 'core-js/modules/es6.function.bind';
// JS's startsWith() -- support for IE and old Opera
import 'core-js/modules/es6.string.starts-with';

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
import './bootstrap';

window.ws = RealtimeFactory();

import './components/notifications.js';
import './components/pm.js';
import Config from './libs/config';
import Router from './libs/router';

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
    }).on('/Praca/Application/*', () => {
        require.ensure([],
            require => {
                require('./pages/job/application');
            },
            'application'
        );
    });

    r.resolve();
});

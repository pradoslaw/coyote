import {RealtimeFactory} from './libs/realtime.js';
import './components/dropdown.js';
import './components/scrolltop.js';
import './components/breadcrumb.js';
import './components/state.js';
import './components/declination.js';
import './components/date.js';
import './components/vcard.js';
import './components/popover.js';
import './components/flag.js';
import './bootstrap';

window.ws = RealtimeFactory();

import './components/notifications.js';
import './components/pm.js';

$(function() {
    'use strict';

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

function Realtime() {
    'use strict';

    /**
     * default interal between retries
     *
     * @type {number}
     */
    var DEFAULT_INTERVAL = 5000;

    /**
     * maximum number of failure retries before giving up
     *
     * @type {number}
     */
    var MAX_RETRIES = 50;

    var self = this;
    var handler = null;
    var retries = 0;

    this._callbacks = {};

    this.on = function(event, fn) {
        (self._callbacks[event] = self._callbacks[event] || []).push(fn);
        return self;
    };

    this.emit = function(event, data, handler) {
        if (self._callbacks[event]) {
            var callbacks = self._callbacks[event].slice(0);

            for (var i = 0, len = callbacks.length; i < len; ++i) {
                callbacks[i].apply(this, [data, handler]);
            }
        }

        return self;
    };

    function connect() {
        handler = new WebSocket(
            (window.location.protocol === 'https:' ? 'wss' : 'ws') + '://' + _config.ws + '/realtime?token=' + _config.token
        );

        handler.onopen = function (e) {
            retries = 0;
        };

        handler.onmessage = function (e) {
            var data = JSON.parse(e.data);

            if (data.event) {
                self.emit(data.event, data.data, handler);
            }
        };

        handler.onclose = function (e) {
            if (++retries < MAX_RETRIES) {
                setTimeout(connect, DEFAULT_INTERVAL * retries);
            }
        };
    }

    if (typeof _config.ws !== 'undefined' && ('WebSocket' in window && window.WebSocket !== null)) {
        connect();
    }
}

// global object
var ws = new Realtime();

// response to the heartbeat event
ws.on('hb', function(data, handler) {
    handler.send(data);
});

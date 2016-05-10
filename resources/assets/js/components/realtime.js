function Realtime() {
    'use strict';

    var self = this;
    var handler = null;
    var timerId = null;
    this._callbacks = {};

    this.on = function(event, fn) {
        (self._callbacks[event] = self._callbacks[event] || []).push(fn);
        return self;
    };

    this.emit = function(event, data) {
        if (self._callbacks[event]) {
            var callbacks = self._callbacks[event].slice(0);
            for (var i = 0, len = callbacks.length; i < len; ++i) {
                callbacks[i].apply(this, [data]);
            }
        }

        return self;
    };

    function connect() {
        handler = new WebSocket(
            (window.location.protocol === 'https:' ? 'wss' : 'ws') + '://' + _config.ws + '/realtime?token=' + _config.token
        );

        handler.onopen = function (e) {
            if (timerId !== null) {
                clearInterval(timerId);
                timerId = null;
            }
        };

        handler.onmessage = function (e) {
            var data = JSON.parse(e.data);

            if (data.event) {
                self.emit(data.event, data.data);
            }
        };

        handler.onclose = function (e) {
            if (timerId === null) {
                timerId = setInterval(function() {
                    connect();
                }, 5000);
            }
        };
    }

    if (typeof _config.ws !== 'undefined') {
        connect();
    }
}

// global object
var ws = new Realtime();

function Realtime() {
    'use strict';

    var self = this;
    var handler = null;
    this._callbaks = {};

    this.on = function(event, fn) {
        (self._callbaks[event] = self._callbaks[event] || []).push(fn);
        return self;
    };

    this.emit = function(event, data) {
        if (self._callbaks[event]) {
            var callbacks = self._callbaks[event].slice(0);
            for (var i = 0, len = callbacks.length; i < len; ++i) {
                callbacks[i].apply(this, [data]);
            }
        }

        return self;
    };

    if (typeof _config.ws !== 'undefined') {
        handler = new WebSocket('ws://' + _config.ws + '/realtime?token=' + _config.token);

        handler.onopen = function (e) {
        };

        handler.onmessage = function (e) {
            var data = JSON.parse(e.data);

            if (data.event) {
                self.emit(data.event, data.data);
            }
        };

        handler.onclose = function (e) {
        };
    }
}

// global object
var ws = new Realtime();

/**
 * default interal between retries
 *
 * @type {number}
 */
const DEFAULT_INTERVAL = 5000;

/**
 * maximum number of failure retries before giving up
 *
 * @type {number}
 */
const MAX_RETRIES = 50;

class Realtime {
    constructor() {
        this._callbacks = {};
        this._retries = 0;

        if (typeof _config.ws !== 'undefined' && this._isSupported()) {
            this._connect();
        }
    }

    on(event, fn) {
        (this._callbacks[event] = this._callbacks[event] || []).push(fn);
        return this;
    }

    _emit(event, data, handler) {
        if (this._callbacks[event]) {
            let callbacks = this._callbacks[event].slice(0);

            for (let i = 0, len = callbacks.length; i < len; ++i) {
                callbacks[i].apply(this, [data, handler]);
            }
        }

        return this;
    }

    _connect() {
        this._handler = new WebSocket(
            (window.location.protocol === 'https:' ? 'wss' : 'ws') + '://' + _config.ws + '/realtime?token=' + _config.token
        );

        this._handler.onopen = function (e) {
            this._retries = 0;
        };

        this._handler.onmessage = function (e) {
            let data = JSON.parse(e.data);

            if (data.event) {
                this._emit(data.event, data.data, this._handler);
            }
        };

        this._handler.onclose = function () {
            if (++this._retries < MAX_RETRIES) {
                setTimeout(this._connect, DEFAULT_INTERVAL * this._retries);
            }
        };
    }

    _isSupported() {
        return ('WebSocket' in window && window.WebSocket !== null);
    }
}

export function RealtimeFactory() {
    let realtime = new Realtime();

    // response to the heartbeat event
    realtime.on('hb', function(data, handler) {
        handler.send(data);
    });

    return realtime;
}

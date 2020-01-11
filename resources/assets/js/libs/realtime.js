import Config from './config';

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

/**
 * Path for web socket handler
 *
 * @type {string}
 */
const PATH = '/realtime';

class Realtime {
  constructor(host, token) {
    this._host = host;
    this._token = token;
    this._callbacks = {};
    this._retries = 0;
    this._isConnected = false;

    if (this._isSupported() && this._host) {
      this.connect();
    }
  }

  on(event, fn) {
    (this._callbacks[event] = this._callbacks[event] || []).push(fn);

    return this;
  }

  whisper(channel, event, data) {
    this._handler.send(JSON.stringify({channel, event: `client-${event}`, data}));
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

  connect() {
    this._handler = new WebSocket(
      (window.location.protocol === 'https:' ? 'wss' : 'ws') + '://' + this._host + PATH + '?token=' + this._token
    );

    this._handler.onopen = () => {
      this._retries = 0;
      this._isConnected = true;
    };

    this._handler.onmessage = e => {
      let data = JSON.parse(e.data);

      if (data.event) {
        this._emit(data.event, data.data, this._handler);
      }
    };

    this._handler.onclose = () => {
      this._isConnected = false;

      if (++this._retries < MAX_RETRIES) {
        setTimeout(() => this.connect(), DEFAULT_INTERVAL * this._retries);
      }
    };
  }

  disconnect() {
    this._retries = MAX_RETRIES + 1;
    this._isConnected = false;

    this._handler.close();
  }

  get isConnected() {
    return this._isConnected;
  }

  _isSupported() {
    return ('WebSocket' in window && window.WebSocket !== null);
  }
}

class RealtimeFactory {
  constructor() {
    if (!RealtimeFactory.instance) {
      let realtime = new Realtime(Config.get('ws'), Config.get('token'));

      // response to the heartbeat event
      realtime.on('hb', function (data, handler) {
        handler.send(JSON.stringify({event: 'hb', data: 'hb'}));
      });

      realtime.on('exit', function () {
        realtime.disconnect();
      });

      RealtimeFactory.instance = realtime;
    }

    return RealtimeFactory.instance;
  }
}

export default new RealtimeFactory();

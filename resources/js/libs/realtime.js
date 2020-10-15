import store from '../store';

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

export const SOCKET_ID = Math.random().toString(32).substr(2);

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
    this._handler.send(JSON.stringify({channel, event, data}));
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
      (window.location.protocol === 'https:' ? 'wss' : 'ws') + `://${this._host}/realtime?token=${this._token}`
    );

    this._handler.onopen = () => {
      this._retries = 0;
      this._isConnected = true;
    };

    this._handler.onmessage = e => {
      let data = JSON.parse(e.data);

      if (data.socket !== SOCKET_ID && data.event) {
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
      let realtime = new Realtime(window.__INITIAL_STATE.ws, store.state.user.token);

      // response to the heartbeat event
      realtime.on('hb', (data, handler) => handler.send(data));
      realtime.on('exit', () => realtime.disconnect());

      RealtimeFactory.instance = realtime;
    }

    return RealtimeFactory.instance;
  }
}

export default new RealtimeFactory();

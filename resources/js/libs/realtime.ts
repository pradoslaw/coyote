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

const WEBSOCKET_OPEN = 1;

export const SOCKET_ID = Math.random().toString(32).substr(2);

type CallbackType = {
  [key: string]: any;
}

class Channel {
  private readonly websocket: WebSocket;
  private readonly name: string;
  private callbacks: CallbackType = {};

  constructor(websocket: WebSocket, name: string) {
    this.websocket = websocket;
    this.name = name;

    this.subscribe();
  }

  on(event: string, fn: any) {
    (this.callbacks[event] = this.callbacks[event] || []).push(fn);

    return this;
  }

  whisper(event: string, data: any) {
    this.websocket.send(JSON.stringify({ channel: this.name, event, data }));
  }

  dispatch(event: string, data: any) {
    if (this.callbacks[event]) {
      let callbacks = this.callbacks[event].slice(0);

      for (let i = 0, len = callbacks.length; i < len; ++i) {
        callbacks[i].apply(this, [data, this.websocket]);
      }
    }

    return this;
  }

  subscribe() {
    if (this.websocket.readyState === WEBSOCKET_OPEN) {
      this.websocket.send(`subscribe:${this.name}`);
    }
  }
}

type ChannelType = {
  [key: string]: Channel;
}

class Realtime {
  private websocket!: WebSocket;
  private host: string;
  private token: string;
  public isConnected = false;
  private retries = 0;
  public channels: ChannelType = {};

  constructor(host, token) {
    this.host = host;
    this.token = token;

    if (this.isSupported() && this.host) {
      this.connect();
    }
  }

  subscribe(channelName) {
    if (!(channelName in this.channels)) {
      this.channels[channelName] = new Channel(this.websocket, channelName);
    }

    return this.channels[channelName];
  }

  connect() {
    this.websocket = new WebSocket(
      (window.location.protocol === 'https:' ? 'wss' : 'ws') + `://${this.host}/realtime?token=${this.token}`
    );

    this.websocket.onopen = () => {
      this.retries = 0;
      this.isConnected = true;

      for (let name of Object.keys(this.channels)) {
        this.channels[name].subscribe();
      }
    };

    this.websocket.onmessage = e => {
      const data = JSON.parse(e.data);

      if (data.socket === SOCKET_ID || !data.event) {
        return;
      }

      for (let name of Object.keys(this.channels)) {
        this.channels[name].dispatch(data.event, data.data);
      }


    };

    this.websocket.onclose = () => {
      this.isConnected = false;

      if (++this.retries < MAX_RETRIES) {
        setTimeout(() => this.connect(), DEFAULT_INTERVAL * this.retries);
      }
    };
  }

  disconnect() {
    this.retries = MAX_RETRIES + 1;
    this.isConnected = false;

    this.websocket.close();
  }

  private isSupported() {
    return ('WebSocket' in window && window.WebSocket !== null);
  }
}
//
// class RealtimeFactory {
//   private static instance: Realtime;
//
//   constructor() {
//     if (!RealtimeFactory.instance) {
//
//       let realtime = new Realtime(window.__INITIAL_STATE.ws, store.state.user.token);
//
//       // response to the heartbeat event
//       // realtime.on('exit', () => realtime.disconnect());
//
//       RealtimeFactory.instance = realtime;
//     }
//
//     return RealtimeFactory.instance;
//   }
// }

// @ts-ignore
let realtime = new Realtime(window.__INITIAL_STATE.ws, store.state.user.token);

export default realtime;

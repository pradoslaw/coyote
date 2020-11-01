import store from '../store';
import Channels from "./websocket/channels";
import Transport, { WebSocketData } from "./websocket/transport";

export const SOCKET_ID = Math.random().toString(32).substr(2);

class Realtime {
  private host: string;
  private token: string;

  private readonly channels: Channels;
  private readonly transport: Transport;

  constructor(host, token) {
    this.host = host;
    this.token = token;
    this.channels = new Channels();
    this.transport = new Transport((window.location.protocol === 'https:' ? 'wss' : 'ws') + `://${this.host}/realtime?token=${this.token}`);

    if (this.isSupported() && this.host) {
      this.transport.openHandler = () => this.channels.subscribe();
      this.transport.closeHandler = () => this.channels.unsubscribe();
      this.transport.messageHandler = (data: WebSocketData) => {
        // for (let name of Object.keys(this.channels)) {
        //   this.channels[name].dispatch(data.event, data.data);
        // }
      }

      this.transport.connect();
    }
  }

  subscribe(channelName) {
    return this.channels.add(this.transport, channelName);
  }

  private isSupported() {
    return ('WebSocket' in window && window.WebSocket !== null);
  }
}

// @ts-ignore
let realtime = new Realtime(window.__INITIAL_STATE.ws, store.state.user.token);

export default realtime;

import Channels from "./websocket/channels";
import Transport, { WebSocketData } from "./websocket/transport";

export const SOCKET_ID = Math.random().toString(32).substr(2);

class Realtime {
  private readonly channels: Channels;
  private readonly transport: Transport;

  constructor(url: string | null | undefined) {
    this.channels = new Channels();
    this.transport = new Transport(url);

    if (this.isSupported() && url) {
      this.transport.openHandler = () => this.channels.subscribe();
      this.transport.closeHandler = () => this.channels.unsubscribe();
      this.transport.messageHandler = (data: WebSocketData) => {
        this.channels.collection().forEach(channel => channel.handleEvent(data.event, data.data));
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

const websocketUrl = document.querySelector('meta[name="websocket-url"]')?.getAttribute('content');

export default new Realtime(websocketUrl);

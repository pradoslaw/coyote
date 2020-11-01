import { SOCKET_ID } from "../realtime";

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

export interface WebSocketData {
  socket: string;
  channel: string;
  event: string;
  data: string;
}

export default class Transport {
  public isConnected = false;
  public openHandler;
  public closeHandler;
  public messageHandler;
  private websocket?: WebSocket;
  private retries = 0;
  private readonly url;

  constructor(url: string | undefined | null) {
    this.url = url;
  }

  connect() {
    this.websocket = new WebSocket(this.url);

    this.websocket.onopen = () => {
      this.retries = 0;
      this.isConnected = true;

      this.openHandler();
    };

    this.websocket.onmessage = e => {
      const data: WebSocketData = JSON.parse(e.data);

      if (data.socket === SOCKET_ID || !data.event) {
        return;
      }

      this.messageHandler(data);
    };

    this.websocket.onclose = () => {
      this.isConnected = false;
      this.closeHandler();

      if (++this.retries < MAX_RETRIES) {
        setTimeout(() => this.connect(), DEFAULT_INTERVAL * this.retries);
      }
    };
  }

  send(data: string) {
    this.websocket?.send(data);
  }

  get readyState() {
    return this.websocket?.readyState;
  }
}

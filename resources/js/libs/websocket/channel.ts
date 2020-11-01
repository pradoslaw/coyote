import Transport from "./transport";

type CallbackType = {
  [key: string]: any;
}

export default class Channel {
  private readonly transport: Transport;
  private readonly name: string;
  private callbacks: CallbackType = {};
  private isSubscribed = false;

  constructor(transport: Transport, name: string) {
    this.transport = transport;
    this.name = name;

    this.subscribe();
  }

  on(event: string, fn: any) {
    (this.callbacks[event] = this.callbacks[event] || []).push(fn);

    return this;
  }

  whisper(event: string, data: any) {
    this.transport.send(JSON.stringify({ channel: this.name, event, data }));
  }

  handleEvent(event: string, data: any) {
    if (this.callbacks[event]) {
      let callbacks = this.callbacks[event].slice(0);

      for (let i = 0, len = callbacks.length; i < len; ++i) {
        callbacks[i].apply(this, [data]);
      }
    }

    return this;
  }

  subscribe() {
    if (this.transport.readyState === WebSocket.OPEN && !this.isSubscribed) {
      this.transport.send(`subscribe:${this.name}`);
      this.isSubscribed = true;
    }
  }

  unsubscribe() {
    this.isSubscribed = false;
  }
}

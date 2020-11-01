import Channel from "./channel";
import Transport from "./transport";

type ChannelType = {
  [key: string]: Channel;
}

export default class Channels {
  public channels: ChannelType = {};

  add(transport: Transport, name: string) {
    if (!(name in this.channels)) {
      this.channels[name] = new Channel(transport, name);
    }

    return this.channels[name];
  }

  subscribe() {
    this.collection().forEach(channel => channel.subscribe());
  }

  unsubscribe() {
    this.collection().forEach(channel => channel.unsubscribe());
  }

  collection() {
    return Object.values(this.channels);
  }
}

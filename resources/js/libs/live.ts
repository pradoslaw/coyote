import { Microblog, Post, PostComment } from "../types/models";

type Payload = Microblog | Post | PostComment;

export interface Observer {
  update(microblog: Payload): void;
}

export class LiveNotification {
  private observers: Observer[] = [];

  attach(observer: Observer) {
    this.observers.push(observer);
  }

  notify(payload: Payload) {
    for (const observer of this.observers) {
      observer.update(payload);
    }
  }
}

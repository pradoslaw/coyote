import { Microblog, Post, PostComment } from "@/types/models";
import { default as ws } from "./realtime";
import Prism from "prismjs";
import store from '../store';
import Vue from 'vue';
import Channel from "@/libs/websocket/channel";
import axios from 'axios';

export type Payload = Microblog | Post | PostComment;

export interface Observer {
  update(payload: Payload): void;
}

export class MicroblogSaved implements Observer {
  update(microblog: Microblog) {
    const existing = store.state.microblogs.data[microblog.id!];

    if (!existing || existing.is_editing) {
      return; // do not add new entries live (yet)
    }

    store.commit('microblogs/update', microblog);
  }
}

export class MicroblogCommentSaved implements Observer {
  update(payload: Microblog) {
    if (!payload.parent_id) {
      return;
    }

    const parent = store.state.microblogs.data[payload.parent_id];
    const existing = parent?.comments[payload.id!];

    if (!parent || existing?.is_editing === true) {
      return;
    }

    if (!existing) {
      payload.is_read = false;
    }

    store.commit(`microblogs/${payload.id! in parent.comments ? 'updateComment' : 'addComment'}`, { parent, comment: payload });
  }
}

export class PostCommentSaved implements Observer {
  update(payload: PostComment) {
    const post = store.state.posts.data[payload.post_id];
    const existing = post?.comments[payload.id!];

    if (!post || existing?.is_editing === true) {
      return;
    }

    if (!existing) {
      payload.is_read = false;
    }

    store.commit(`posts/${payload.id! in post.comments ? 'updateComment' : 'addComment'}`, { post, comment: payload });
  }
}

export class PostSaved implements Observer {
  update(payload: Post) {
    const existing = store.state.posts.data[payload.id!];

    if (!existing) {
      payload.is_read = false;

      // user must be on the last page of topic
      if (store.getters['posts/currentPage'] < store.getters['posts/totalPages']) {
        return;
      }
    }

    if (!existing) {
      store.commit(`posts/add`, payload);
      const topic = store.getters['topics/topic'];

      Vue.nextTick(() => document.getElementById(`id${payload.id}`)!.addEventListener('mouseover', () => store.dispatch('topics/mark', topic), {once: true}))

      axios.get(`/Forum/Post/${payload.id}`).then(response => store.commit('posts/update', response.data));

      return;
    }

    store.commit(`posts/update`, payload);
  }
}

export class Subscriber {
  private channel: Channel;

  constructor(channelName: string) {
    this.channel = ws.subscribe(channelName);
  }

  subscribe(event: string, observer: Observer) {
    this.channel.on(event, payload => {
      if (store.getters['user/isBlocked'](payload.user!.id)) {
        return;
      }

      observer.update(payload);

      Vue.nextTick(() => Prism.highlightAll());
    });
  }
}

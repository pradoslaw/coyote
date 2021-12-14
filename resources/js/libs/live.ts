import { Microblog, Post, PostComment, MicroblogVoters, PostVoters } from "@/types/models";
import { default as ws } from "./realtime";
import Prism from "prismjs";
import store from '../store';
import Vue from 'vue';
import Channel from "@/libs/websocket/channel";
import { getPost } from '@/api';

export type Payload = Microblog | Post | PostComment | MicroblogVoters | PostVoters;

export interface Observer {
  update(payload: Payload): void;
}

abstract class MicroblogObserver {
  get microblogs(): Microblog[] {
    return store.state.microblogs.data;
  }
}

abstract class PostObserver {
  get posts(): Post[] {
    return store.state.posts.data;
  }
}

export class MicroblogSaved extends MicroblogObserver implements Observer {
  update(microblog: Microblog) {
    const existing = this.microblogs[microblog.id!];

    if (!existing || existing.is_editing) {
      return; // do not add new entries live (yet)
    }

    store.commit('microblogs/UPDATE', microblog);
  }
}

export class MicroblogVoted extends MicroblogObserver implements Observer {
  update(payload: MicroblogVoters) {
    const existing = this.microblogs[payload.id!] ?? this.microblogs[payload.parent_id!]?.comments[payload.id];

    if (!existing) {
      return;
    }

    store.dispatch('microblogs/updateVoters', { microblog: existing, users: payload.users });
  }
}

export class MicroblogCommentSaved extends MicroblogObserver implements Observer {
  update(payload: Microblog) {
    if (!payload.parent_id) {
      return;
    }

    const parent = this.microblogs[payload.parent_id];
    const existing = parent?.comments[payload.id!];

    if (!parent || existing?.is_editing === true) {
      return;
    }

    if (!existing) {
      payload.is_read = false;
    }

    store.commit(`microblogs/${payload.id! in parent.comments ? 'UPDATE_COMMENT' : 'ADD_COMMENT'}`, { parent, comment: payload });
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
    const existing = store.state.posts.data[payload.id];

    if (!existing) {
      payload.is_read = false;

      // user must be on the last page of topic
      if (store.getters['posts/currentPage'] < store.getters['posts/totalPages']) {
        return;
      }
    }

    if (!existing) {
      const topic = store.getters['topics/topic'];

      getPost(payload.id).then(({ data }) => {
        store.commit(`posts/add`, data);

        Vue.nextTick(() => document.getElementById(`id${payload.id}`)!.addEventListener('mouseover', () => store.dispatch('topics/mark', topic), {once: true}))
      });

      return;
    }

    store.commit(`posts/update`, payload);
  }
}

export class PostVoted extends PostObserver implements Observer {
  update(payload: PostVoters) {
    const existing = this.posts[payload.id!];

    if (!existing) {
      return;
    }

    store.dispatch('posts/updateVoters', { post: existing, users: payload.users });
  }
}

export class Subscriber {
  private channel: Channel;

  constructor(channelName: string) {
    this.channel = ws.subscribe(channelName);
  }

  subscribe(event: string, observer: Observer) {
    this.channel.on(event, payload => {
      if (store.getters['user/isBlocked'](payload.user?.id)) {
        return;
      }

      observer.update(payload);

      Vue.nextTick(() => Prism.highlightAll());
    });
  }
}

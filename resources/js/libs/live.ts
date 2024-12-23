import Prism from "prismjs";

import {getPost, getPostComment} from '../api/posts';
import store from '../store';
import {Microblog, MicroblogVoters, Post, PostComment, PostVoters} from "../types/models";
import {nextTick} from "../vue";

import {default as ws} from "./realtime";
import Channel from "./websocket/channel";

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

    store.dispatch('microblogs/updateVoters', {microblog: existing, users: payload.users});
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

    store.commit(`microblogs/${payload.id! in parent.comments ? 'UPDATE_COMMENT' : 'ADD_COMMENT'}`, {parent, comment: payload});
  }
}

export class PostCommentSaved implements Observer {
  update(payload: PostComment): void {
    const postId = payload.post_id;
    if (!store.getters['posts/exists'](postId)) {
      return;
    }
    if (store.getters['posts/commentExists'](postId, payload.id)) {
      if (!store.getters['posts/commentIsEditing'](postId, payload.id)) {
        store.commit('posts/updateComment', {postId, comment: payload});
      }
    } else {
      payload.is_read = false;
      getPostComment(payload.id).then(({data}) => {
        store.commit('posts/addComment', {postId, comment: data});
      });
    }
  }
}

export class PostSaved implements Observer {
  update(payload: Post): void {
    const postId: number = payload.id;
    if (store.getters['posts/exists'](postId)) {
      store.commit('posts/update', payload);
    } else {
      payload.is_read = false;
      if (store.getters['posts/isLastPage']) {
        getPost(postId).then(({data}): void => {
          store.commit('posts/add', data);
          nextTick(() => {
            const post = document.getElementById(`id${postId}`)!;
            post.addEventListener('mouseover', () => {
              return store.dispatch('topics/mark', store.getters['topics/topic']);
            }, {once: true});
          });
        });
      }
    }
  }
}

export class PostVoted extends PostObserver implements Observer {
  update(payload: PostVoters) {
    const postId = payload.id!;
    if (store.getters['posts/exists'](postId)) {
      store.dispatch('posts/updateVoters', {
        postId,
        users: payload.users,
      });
    }
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

      nextTick(() => Prism.highlightAll());
    });
  }
}

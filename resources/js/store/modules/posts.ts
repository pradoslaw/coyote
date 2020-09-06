import axios from "axios";
import { Post, Forum, Topic, PostComment, PostAttachment } from "../../types/models";
import Vue from "vue";

type PostObj = {[key: number]: Post};
type ParentChild = { post: Post, comment: PostComment };
type PostWithAttachment = { post: Post, attachment: PostAttachment };

const state: { data: PostObj, links: null, meta: null, forum?: Forum, topic?: Topic } = {data: {}, links: null, meta: null}

const getters = {
  posts: state => state.data,
  exists: state => (id: number) => id in state.data
}

const mutations = {
  init(state, { pagination, forum, topic }) {
    state = Object.assign(state, pagination, { forum, topic });
  },

  add(state, post: Post) {
    Vue.set(state.data, post.id!, post);
  },

  update(state, post: Post) {
    let { text, html } = post; // update only text and html version

    Vue.set(state.data, post.id!, {...state.data[post.id!], ...{text, html}})
  },

  delete(state, post: Post) {
    post.deleted_at = new Date();
  },

  addComment(state, { post, comment}: ParentChild) {
    if (Array.isArray(post.comments)) {
      Vue.set(post, "comments", {});
    }

    Vue.set(post.comments, comment.id!, comment);
  },

  updateComment(state, { post, comment}: ParentChild) {
    let { text, html } = comment; // update only text and html version

    Vue.set(post.comments, comment.id!, {...post.comments[comment.id], ...{text, html}});
  },

  deleteComment(state, comment: PostComment) {
    Vue.delete(state.data[comment.post_id!].comments, comment.id!);
  },

  addAttachment(state, { post, attachment }: { post: Post, attachment: PostAttachment }) {
    post.attachments.push(attachment);
  },

  deleteAttachment(state, { post, attachment }: PostWithAttachment) {
    post.attachments.splice(post.attachments.findIndex(item => item.file === attachment.file));
  },

  restore(state, post: Post) {
    post.deleted_at = null;

    delete post.delete_reason;
    delete post.deleter_name;
  },

  vote(state, post: Post) {
    if (post.is_voted) {
      post.is_voted = false;
      post.score -= 1;
    }
    else {
      post.is_voted = true;
      post.score += 1;
    }
  },

  accept(state, post: Post) {
    if (post.is_accepted) {
      post.is_accepted = false;
    }
    else {
      let values: Post[] = Object.values(state.data);

      // user choose different option
      for (let item of values) {
        if (item.is_accepted) {
          item.is_accepted = false;
        }
      }

      post.is_accepted = true;
    }
  },

  subscribe(state, post: Post) {
    post.is_subscribed = !post.is_subscribed;
  }
}

const actions = {
  vote({ commit }, post: Post) {
    commit('vote', post);

    return axios.post(`/Forum/Post/Vote/${post.id}`).catch(() => commit('vote', post));
  },

  accept({ commit, getters }, post: Post) {
    commit('accept', post);

    return axios.post(`/Forum/Post/Accept/${post.id}`).catch(() => commit('accept', post));
  },

  subscribe({ commit }, post: Post) {
    commit('subscribe', post);

    return axios.post(`/Forum/Post/Subscribe/${post.id}`).catch(() => commit('subscribe', post));
  },

  save({ commit, state, getters }, { post, topic }: { post: Post, topic: Topic }) {
    const input = {
      text: post.text,
      subject: topic?.subject,
      is_sticky: topic?.is_sticky,
      is_subscribed: topic?.is_subscribed,
      attachments: post.attachments
    };

    return axios.post(`/Forum/${state.forum.slug}/Submit/${topic?.id || ''}/${post?.id || ''}`, input).then(result => {
      commit(getters.exists(result.data.id) ? 'update' : 'add', result.data);

      return result;
    });
  },

  saveComment({ state, commit, getters }, comment: PostComment) {
    return axios.post(`/Forum/Comment/${comment.id || ''}`, comment).then(result => {
      const post = state.data[result.data.post_id!];

      commit(post.comments[result.data.id!] ? 'updateComment' : 'addComment', { post, comment: result.data });
    });
  },

  delete({ commit }, { post, reasonId }: { post: Post, reasonId: number | null }) {
    return axios.delete(`/Forum/Post/Delete/${post.id}`, { data: { reason: reasonId } }).then(() => commit('delete', post));
  },

  deleteComment({ commit }, comment: PostComment) {
    return axios.delete(`/Forum/Comment/Delete/${comment.id}`).then(() => commit('deleteComment', comment));
  },

  restore({ commit }, post: Post) {
    return axios.post(`/Forum/Post/Restore/${post.id}`).then(() => commit('restore', post));
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};


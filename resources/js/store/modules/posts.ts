import axios from "axios";
import { Post, Forum, Topic } from "../../types/models";
import Vue from "vue";

type PostObj = {[key: number]: Post};

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

  restore(state, post: Post) {
    post.deleted_at = null;
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
    const input = { text: post.text, subject: topic?.subject, is_sticky: topic?.is_sticky, is_subscribed: topic?.is_subscribed };

    return axios.post(`/Forum/${state.forum.slug}/Submit/${topic?.id || ''}/${post?.id || ''}`, input).then(result => {
      commit(getters.exists(result.data.id) ? 'update' : 'add', result.data);

      return result;
    });
  },

  delete({ commit }, { post, reasonId }: { post: Post, reasonId: number | null }) {
    return axios.delete(`/Forum/Post/Delete/${post.id}`, { data: { reason: reasonId } }).then(() => commit('delete', post));
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


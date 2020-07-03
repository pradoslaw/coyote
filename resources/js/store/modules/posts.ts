import axios from "axios";
import {Post, Forum, Paginator, Media, Microblog} from "../../types/models";
import Vue from 'vue';

type PostObj = {[key: number]: Post};

const state: { data: PostObj, links: null, meta: null, forum?: Forum } = {data: {}, links: null, meta: null}

const getters = {
  posts: state => state.data
}

const mutations = {
  init(state, { pagination, forum }) {
    console.log(pagination);

    // pagination.data = pagination.data
      // .map(post => (post.comments = post.comments.keyBy('id'), post))
      // .keyBy('id');

    state = Object.assign(state, pagination, { forum });
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

  save({ commit, state, getters }, post: Post) {
    return axios.post(`/Forum/${state.forum.slug}/Submit/${post?.id || ''}`, post).then(result => {
      commit(getters.exists(result.data.id) ? 'update' : 'add', result.data)
    });
  },
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};


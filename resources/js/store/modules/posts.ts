import axios from "axios";
import {Post, Paginator, Media, Microblog} from "../../types/models";
import Vue from 'vue';


const state = {
  data: [],
  links: null,
  meta: null
};

const getters = {
  posts: state => state.data
}

const mutations = {
  init(state, { pagination }) {
    console.log(pagination);

    // pagination.data = pagination.data
      // .map(post => (post.comments = post.comments.keyBy('id'), post))
      // .keyBy('id');

    state = Object.assign(state, pagination);
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
  }
}

const actions = {
  vote({ commit }, post: Post) {
    commit('vote', post);

    return axios.post(`/Forum/Post/Vote/${post.id}`).catch(() => commit('vote', post));
  },
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};


import axios from "axios";
import Vue from "vue";
import { Comment } from '@/types/models';

const state = {};

const mutations = {
  INIT(state, comments) {
    state = Object.assign(state, comments);
  },

  UPDATE(state, comment: Comment) {
    if (comment.parent_id) {
      const parent = state[comment.parent_id];

      if (Array.isArray(parent.children)) {
        Vue.set(parent, "children", {});
      }

      Vue.set(parent.children, comment.id, comment);
    } else {
      Vue.set(state, comment.id, comment);
    }
  },

  DELETE(state, comment) {
    if (comment.parent_id) {
      let parent = state[comment.parent_id];

      Vue.delete(parent.children, comment.id);
    } else {
      Vue.delete(state, comment.id);
    }
  }
}

const actions = {
  save({ commit }, comment) {
    return axios.post(`/Comment/${comment.id || ''}`, comment).then(response => (commit('UPDATE', response.data), response));
  },

  delete({ commit }, comment) {
    return axios.delete(`/Comment/${comment.id}`).then(() => commit('DELETE', comment));
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

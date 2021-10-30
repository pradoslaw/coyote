import axios from "axios";
import Vue from "vue";
import { Comment } from '@/types/models';

const state = { comments: [] };

const mutations = {
  INIT(state, comments) {
    state.comments = comments;
  },

  UPDATE(state, comment: Comment) {
    if (comment.parent_id) {
      const parent = state.comments[comment.parent_id];

      if (Array.isArray(parent.children)) {
        Vue.set(parent, "children", {});
      }

      Vue.set(parent.children, comment.id, comment);
    } else {
      Vue.set(state.comments, comment.id, comment);
    }
  },

  DELETE(state, comment) {
    if (comment.parent_id) {
      let parent = state.comments[comment.parent_id];

      Vue.delete(parent.children, comment.id);
      console.log(parent)
    } else {
      Vue.delete(state.comments, comment.id);
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

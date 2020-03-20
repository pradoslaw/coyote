import axios from "axios";

const state = {
  topics: []
};

const mutations = {
  init(state, topics) {
    state.topics = topics;
  },

  mark(state, topic) {
    topic.is_read = true;
  }
};

const actions = {

  mark({ commit }, category) {
    commit('mark', category);

    axios.post(`/Forum/${category.slug}/Mark`);
  },

  markAll({ state, commit }) {
    axios.post('/Forum/Mark');

    state.topics.forEach(topic => commit('mark', topic));
  },


};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

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
  },

  subscribe(state, topic) {
    if (topic.is_subscribed) {
      topic.subscribers--;
    }
    else {
      topic.subscribers++;
    }

    topic.is_subscribed = !topic.is_subscribed;
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

  subscribe({ commit }, topic) {
    commit('subscribe', topic);
  }
};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

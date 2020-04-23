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

  markAll(state) {
    state.topics.forEach(topic => topic.is_read = true);
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
  mark({ commit }, topic) {
    commit('mark', topic);

    axios.post(`/Forum/Topic/Mark/${topic.id}`);
  },

  markAll({ state, commit }) {
    commit('markAll');

    axios.post(`/Forum/${state.topics[0].forum.slug}/Mark`);
  },

  subscribe({ commit }, topic) {
    commit('subscribe', topic);

    axios.post(`/Forum/Topic/Subscribe/${topic.id}`);
  }
};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

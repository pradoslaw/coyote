import axios from "axios";
import { Topic } from '../../types/models';

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
  },

  lock(state, topic: Topic) {
    topic.is_locked = !topic.is_locked;
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
  },

  lock({ commit }, topic) {
    commit('lock', topic);

    axios.post(`/Forum/Topic/Lock/${topic.id}`);
  },

  move({ commit }, { topic, forumId, reasonId }) {
    return axios.post(`/Forum/Topic/Move/${topic.id}`, { id: forumId, reason_id: reasonId });
  }
};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

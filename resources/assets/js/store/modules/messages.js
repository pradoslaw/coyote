import axios from "axios";

const state = {
  messages: []
};

const mutations = {
  init(state, messages) {
    state.messages = messages;
  },

  add(state, message) {
    state.messages.push(message);
  },

  remove(state, message) {
    const index = state.messages.findIndex(item => item.id === message.id);

    state.messages.splice(index, 1);
  }
};

const actions = {
  add ({ commit }, data) {
    return axios.post('/User/Pm/Submit', data).then(result => {
      commit('add', result.data);
    });
  },

  remove ({ commit }, message) {
    return axios.delete(`/User/Pm/Delete/${message.id}`).then(() => {
      commit('remove', message);
    });
  },

  mark ({ commit }, message) {
    message.read_at = new Date();

    return axios.post(`/User/Pm/Mark/${message.id}`);
  }
};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

import axios from "axios";

const state = {
  messages: [],
  offset: 0,
  currentPage: 0,
  total: 0,
  perPage: 0
};

const mutations = {
  init(state, { messages, total, currentPage, perPage }) {
    state.messages = messages;
    state.currentPage = currentPage;
    state.total = total;
    state.perPage = perPage;
    state.offset = messages.length;
  },

  add(state, message) {
    state.messages.push(message);
  },

  merge(state, messages) {
    if (state.messages === null) {
      return;
    }

    state.messages = messages.concat(state.messages);
    state.offset += messages.length;
  },

  remove(state, message) {
    const index = state.messages.findIndex(item => item.id === message.id);

    state.messages.splice(index, 1);
  },

  mark(state, message) {
    message.read_at = new Date();
  },
};

const actions = {
  add ({ commit }, data) {
    return axios.post('/User/Pm/Submit', data).then(response => {
      commit('add', response.data);

      return response;
    });
  },

  remove ({ commit }, message) {
    return axios.delete(`/User/Pm/Delete/${message.id}`).then(() => {
      commit('remove', message);
    });
  },

  trash ({ commit, state }, message) {
    return axios.delete(`/User/Pm/Trash/${message.user.id}`).then(() => {
      commit('remove', message);

      state.total -= 1;
    });
  },

  mark ({ commit }, message) {
    commit('mark', message);

    return axios.post(`/User/Pm/Mark/${message.id}`);
  },

  loadMore ({ state, commit }, authorId) {
    return axios.get('/User/Pm/Infinity', {params: {author_id: authorId, offset: state.offset}}).then(response => {
      commit('merge', response.data.data);

      return response;
    });
  },

  paginate ({ commit }, page) {
    return axios.get('/User/Pm', {params: {page}}).then(response => {
      commit('init', {messages: response.data.messages, total: response.data.total, currentPage: response.data.current_page, perPage: response.data.per_page});

      return response;
    });
  }
};

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

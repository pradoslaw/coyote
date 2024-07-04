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

  reset(state) {
    state.messages = [];
    state.offset = 0;
    state.currentPage = 0;
    state.total = 0;
    state.perPage = 0;
  },

  add(state, message) {
    state.messages.push(message);
    state.offset += 1;
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
    state.offset -= 1;
  },

  mark(state, message) {
    const date = new Date();
    date.setSeconds(date.getSeconds() - 1); // subtract one seconds so we can display "1 seconds ago" instade of "0 seconds ago"

    message.read_at = date;
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

    return axios.post<any>(`/User/Pm/Mark/${message.id}`).then(response => commit('inbox/SET_COUNT', response.data.count, { root: true }));
  },

  loadMore ({ state, commit }, authorId) {
    return axios.get<any>('/User/Pm/Infinity', {params: {author_id: authorId, offset: state.offset}}).then(response => {
      commit('merge', response.data.data);

      return response;
    });
  },

  paginate ({ commit }, page) {
    return axios.get<any>('/User/Pm', {params: {page}}).then(response => {
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

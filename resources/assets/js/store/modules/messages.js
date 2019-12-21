import axios from "axios";

const state = {
  messages: [],
  offset: 0,
  totalPages: 0,
  currentPage: 0
};

const mutations = {
  init(state, { messages, totalPages, currentPage }) {
    state.messages = messages;
    state.totalPages = totalPages;
    state.currentPage = currentPage;
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
  }
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

  mark ({ commit }, message) {
    message.read_at = new Date();

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
      commit('init', {messages: response.data.messages, totalPages: response.data.total_pages, currentPage: response.data.current_page});

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

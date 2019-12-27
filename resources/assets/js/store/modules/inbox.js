import axios from "axios";

const state = {
  messages: null, // initial value must be null to show fa-spinner
  count: 0
};

const getters = {
  isEmpty: state => state.messages === null
};

const mutations = {
  init (state, count) {
    state.count = count;
  },

  set (state, messages) {
    state.messages = messages;
  },

  reset (state) {
    state.messages = null;
  },

  increment (state) {
    state.count += 1;
  },

  decrement (state) {
    state.count = Math.max(0, state.count - 1);
  }
};

const actions = {
  get({ commit }) {
    return axios.get('/User/Pm/Ajax').then(result => {
      commit('set', result.data.pm);
    });
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

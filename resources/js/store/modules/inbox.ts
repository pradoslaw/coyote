import {getInbox} from '../../api/messages';

const state = {
  messages: null, // initial value must be null to show fa-spinner
  count: 0,
};

const getters = {
  isEmpty: state => state.messages === null,
};

const mutations = {
  SET_COUNT(state, count) {
    state.count = count;
  },

  SET_MESSAGES(state, messages) {
    state.messages = messages;
  },

  RESET_MESSAGE(state) {
    state.messages = null;
  },

  MARK(state, message) {
    const date = new Date();
    date.setSeconds(date.getSeconds() - 1); // subtract one seconds so we can display "1 seconds ago" instade of "0 seconds ago"
    message.read_at = date;
  },
};

const actions = {
  get({commit}) {
    return getInbox().then(result => commit('SET_MESSAGES', result.data));
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

import axios from "axios";

const state = {
  notifications: null, // initial value must be null to show fa-spinner
  count: 0,
  offset: 0,
  isOngoing: false
};

const getters = {
  isEmpty: state => state.notifications === null,
  unreadNotifications: state => state.notifications === null ? [] : state.notifications.filter(notification => !notification.is_read)
};

const mutations = {
  init (state, { notifications, count }) {
    state.notifications = Array.isArray(state.notifications) ? state.notifications.concat(notifications) : notifications;
    state.count = count;
    state.offset = notifications !== null ? state.notifications.length : 0;
  },

  remove(state, notification) {
    const index = state.notifications.findIndex(item => item.id === notification.id);

    state.notifications.splice(index, 1);
  },

  reset (state) {
    state.notifications = null; // reset notifications list. user needs to click again to get all notifications from server
    state.offset = 0;
  },

  increment (state) {
    state.count += 1;
  },

  decrement (state) {
    state.count -= 1;
  },

  count (state, count) {
    state.count = count;
  },

  mark (state, notification) {
    notification.is_read = true;
  }
};

const actions = {
  get({ state, commit }) {
    if (state.isOngoing) {
      return;
    }

    state.isOngoing = true;

    return axios.get('/User/Notifications/Ajax', {params: {offset: state.offset}})
      .then(result => commit('init', result.data))
      .finally(() => state.isOngoing = false);
  },

  remove ({ commit }, notification) {
    return axios.delete(`/User/Notifications/Delete/${notification.id}`).then(() => commit('remove', notification));
  },

  markAll({ commit, state }) {
    return axios.post('/User/Notifications/Mark').then(() => {
      state.notifications.forEach(notification => commit('mark', notification));
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

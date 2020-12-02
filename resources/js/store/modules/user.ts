import axios from 'axios';

const state = {
  user: window["__INITIAL_STATE"]?.user || {},
  followers: window["__INITIAL_STATE"]?.followers || []
};

const getters = {
  isAuthorized: state => state.user.id !== undefined,
  dateFormat: state => (defaultFormat = null) => state.user.date_format ?? defaultFormat,
  isBlocked: state => (userId: number) => state.followers.find(follower => follower.user_id === userId && follower.is_blocked)
};

const mutations = {
  update(state, payload) {
    state.user = Object.assign(state.user, payload);
  }
};

const actions = {
  block({ commit }, relatedUserId: number) {
    return axios.post(`/User/Block/${relatedUserId}`);
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

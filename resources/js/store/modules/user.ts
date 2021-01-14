import axios from 'axios';

const state = {
  user: window["__INITIAL_STATE"]?.user || {},
  followers: window["__INITIAL_STATE"]?.followers || []
};

const getters = {
  isAuthorized: state => state.user.id !== undefined,
  dateFormat: state => (defaultFormat = null) => state.user.date_format ?? defaultFormat,
  isBlocked: state => (userId: number) => state.followers.find(follower => follower.user_id === userId && follower.is_blocked),
  follows: state => (userId: number) => state.followers.find(follower => follower.user_id === userId && !follower.is_blocked),
  followers: state => state.followers.find(follower => !follower.is_blocked)
};

const mutations = {
  update(state, payload) {
    state.user = Object.assign(state.user, payload);
  },

  FOLLOW(state, userId) {
    state.followers.push({ is_blocked: false, user_id: userId });
  },

  UNFOLLOW(state, userId) {
    state.followers.splice(state.followers.findIndex(follower => follower.user_id === userId && !follower.is_blocked));
  }
};

const actions = {
  block({ commit }, relatedUserId: number) {
    return axios.post(`/User/Block/${relatedUserId}`);
  },

  follow({ commit }, relatedUserId: number) {
    return axios.post(`/User/Follow/${relatedUserId}`).then(() => commit('FOLLOW', relatedUserId));
  },

  unfollow({ commit }, relatedUserId: number) {
    return axios.post(`/User/Unfollow/${relatedUserId}`).then(() => commit('UNFOLLOW', relatedUserId));
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

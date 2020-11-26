import axios from 'axios';

const state = window["__INITIAL_STATE"]?.user || {};

const getters = {
  isAuthorized: state => state.id !== undefined,
  dateFormat: state => (defaultFormat = null) => state.date_format ? state.date_format : defaultFormat
};

const mutations = {
  update(state, payload) {
    state = Object.assign(state, payload);
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

import { Flag } from '../../types/models';
import axios from "axios";

const state = [];

const getters = {
  filter: state => (resourceId: number) => state.filter(flag => flag.resource_id === resourceId)
}

const mutations = {
  init(state, flags: Flag[] | undefined) {
    state = Object.assign(state, flags);
  },

  delete(state, flag: Flag) {
    state.splice(state.findIndex(_ => _.id === flag.id), 1);
  }
}

const actions = {
  delete({ commit }, flag: Flag) {
    return axios.post(`/Flag/Delete/${flag.id}`).then(() => commit('delete', flag));
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

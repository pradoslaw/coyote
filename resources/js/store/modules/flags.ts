import { Flag } from '../../types/models';
import axios from "axios";

const state = [];

const getters = {
  filter: state => (metadata_id: number) => state.filter(flag => flag.metadata_id === metadata_id)
}

const mutations = {
  init(state, flags: Flag[] | undefined) {
    state = Object.assign(state, flags);
  },

  delete(state, flag: Flag) {
    state.splice(state.find(_ => _.id === flag.id), 1);
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

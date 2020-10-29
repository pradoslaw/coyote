import { Flag } from '../../types/models';

const state = [];

const getters = {
  filter: state => (metadata_id: number) => state.filter(flag => flag.metadata_id === metadata_id)
}

const mutations = {
  init(state, flags: Flag[] | undefined) {
    state = Object.assign(state, flags);
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations
};

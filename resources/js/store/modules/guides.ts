import { Paginator } from "@/types/models";

const state = {
  pagination: {},
  guide: null
}

const mutations = {
  init(state, { guide }) {
    state.guide = guide;
  }
}

const actions = {

}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

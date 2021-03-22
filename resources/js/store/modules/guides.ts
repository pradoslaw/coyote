import { Paginator, Guide } from "@/types/models";

const state = {
  pagination: {},
  guide: null
}

const mutations = {
  init(state, { guide }) {
    state.guide = guide;
  },

  edit(state) {
    state.guide.is_editing = !state.guide.is_editing;
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

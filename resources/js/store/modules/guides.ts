import { Paginator, Guide } from "@/types/models";
import axios from 'axios';

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
  },

  save(state, guide: Guide) {
    state.guide = guide;
  }
}

const actions = {
  save({ state, commit }) {
    axios.post(`/Guide/Submit/${state.guide.id}`).then(response => {
      commit('save', response.data);
      commit('edit');
    });
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

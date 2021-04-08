import { Paginator, Guide, Tag } from "@/types/models";
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
  },

  toggleTag(state, tag: Tag) {
    const index = state.guide.tags!.findIndex(item => item.name === tag.name);

    index > -1 ? state.guide.tags!.splice(index, 1) : state.guide.tags!.push(tag);
  }
}

const actions = {
  save({ state, commit }) {
    axios.post(`/Guide/Submit/${state.guide.id}`, state.guide).then(response => {
      commit('edit');
      commit('save', response.data);
    });
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

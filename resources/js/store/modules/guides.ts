import {Paginator, Guide, Tag, Microblog} from "@/types/models";
import axios from 'axios';
import Vue from 'vue';

const state = {
  pagination: {},
  guide: null
}

const mutations = {
  INIT(state, { guide }) {
    state.guide = guide;
  },

  EDIT(state) {
    Vue.set(state.guide, 'is_editing', !state.guide.is_editing);
  },

  SAVE(state, guide: Guide) {
    state.guide = guide;
  },

  TOGGLE_TAG(state, tag: Tag) {
    const index = state.guide.tags!.findIndex(item => item.name === tag.name);

    index > -1 ? state.guide.tags!.splice(index, 1) : state.guide.tags!.push(tag);
  },

  VOTE(state, guide: Guide) {
    if (guide.is_voted) {
      guide.is_voted = false;
      guide.votes -= 1;
    }
    else {
      guide.is_voted = true;
      guide.votes += 1;
    }
  },
}

const actions = {
  save({ state, commit }) {
    return axios.post(`/Guide/Submit/${state.guide.id || ''}`, state.guide).then(response => {
      commit('EDIT');
      commit('SAVE', response.data);
    });
  },

  vote({ commit, dispatch }, guide: Guide) {
    commit('VOTE', guide);

    return axios.post(`/Guide/Vote/${guide.id}`)
      .catch(() => commit('VOTE', guide));
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

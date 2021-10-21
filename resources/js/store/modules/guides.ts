import {Paginator, Guide, Tag} from "@/types/models";
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
    guide.is_voted = !guide.is_voted;
    guide.votes += (guide.is_voted ? 1 : -1);
  },

  SUBSCRIBE(vote, guide: Guide) {
    guide.is_subscribed = !guide.is_subscribed;
    guide.subscribers += (guide.is_subscribed ? 1 : -1);
  }
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

    return axios.post(`/Guide/Vote/${guide.id}`).catch(() => commit('VOTE', guide));
  },

  subscribe({ commit }, guide: Guide) {
    commit('SUBSCRIBE', guide);

    axios.post(`/Guide/Subscribe/${guide.id}`).catch(() => commit('SUBSCRIBE', guide));
  },
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

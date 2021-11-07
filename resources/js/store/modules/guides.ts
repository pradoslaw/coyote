import { Guide, Tag, Seniority } from "@/types/models";
import axios from 'axios';
import Vue from 'vue';

const state = {
  pagination: {},
  guide: null
}

const getters = {
  currentPage: state => state.pagination.meta.current_page,
  totalPages: state => state.pagination.meta.last_page
}

const mutations = {
  INIT(state, { guide }) {
    state.guide = guide;
  },

  INIT_PAGINATION(state, pagination) {
    state.pagination = pagination;
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

  SUBSCRIBE(state, guide: Guide) {
    guide.is_subscribed = !guide.is_subscribed;
    guide.subscribers += (guide.is_subscribed ? 1 : -1);
  },

  SET_COMMENTS_COUNT(state, { guide, count} ) {
    guide.comments_count = count;
  },

  SET_ROLE(state, { guide, role }: { guide: Guide, role: Seniority }) {
    guide.role = role;
  }
}

const actions = {
  save({ state, commit }) {
    return axios.post(`/Guide/Submit/${state.guide.id || ''}`, state.guide).then(response => {
      commit('EDIT');
      commit('SAVE', response.data);

      return response;
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

  setRole({ commit }, { guide, role }: { guide: Guide, role: Seniority }) {
    commit('SET_ROLE', { guide, role });

    return axios.post(`/Guide/Role/${guide.id}`, { role });
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

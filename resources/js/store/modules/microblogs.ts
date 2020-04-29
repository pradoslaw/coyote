import axios from "axios";
import {Microblog, Paginator} from "../../types/models";

const state = {
  data: [],
  links: null,
  meta: null
};

const getters = {
  microblogs: state => state.data,
  currentPage: state => state.meta.current_page,
  totalPages: state => state.meta.total
}

const mutations = {
  init(state, pagination: Paginator | undefined) {
    if (pagination) {
      state = Object.assign(state, pagination);
    }
  },

  add(state: Paginator, microblog: Microblog | undefined) {
    if (microblog) {
      state.data.push(microblog);
    }
  }
};

const actions = {
  subscribe({ commit }, microblog) {

  },

  delete({ commit }, microblog) {
    axios.post(`/Mikroblogi/Delete/${microblog.id}`).then(() => {

    });
  },

  vote({ commit }, microblog) {

  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

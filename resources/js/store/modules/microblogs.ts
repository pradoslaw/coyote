import axios from "axios";
import { Microblog } from "../../types/models";

const state = {
  microblogs: []
};

const mutations = {
  init(state, microblogs) {
    state.microblogs = microblogs;
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
  mutations,
  actions
};

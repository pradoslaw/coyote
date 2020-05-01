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
  },

  setComments(state, { microblog, comments }) {
    microblog.comments = comments;
  },

  subscribe(state, microblog: Microblog) {
    microblog.is_subscribed = ! microblog.is_subscribed;
  },

  vote(state, microblog: Microblog) {
    microblog.is_voted = ! microblog.is_voted;
  },

  updateVotes(state, { microblog, votes }) {
    microblog.votes = votes;
  }
};

const actions = {
  subscribe({ commit }, microblog: Microblog) {
    commit('subscribe', microblog);

    axios.post(`/Mikroblogi/Subscribe/${microblog.id}`);
  },

  vote({ commit }, microblog: Microblog) {
    commit('vote', microblog);

    axios.post(`/Mikroblogi/Vote/${microblog.id}`).then(result => {
      const votes = result.data;

      commit('updateVotes', { microblog, votes });
    });
  },

  delete({ commit }, microblog) {
    axios.post(`/Mikroblogi/Delete/${microblog.id}`).then(result => {

    });
  },



  loadComments({ commit }, microblog: Microblog) {
    axios.get(`/Mikroblogi/Comment/Show/${microblog.id}`).then(result => {
      let comments = result.data;

      commit('setComments', { microblog, comments });
    })
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

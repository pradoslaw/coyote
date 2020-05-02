import axios from "axios";
import {Microblog, Paginator} from "../../types/models";
import Vue from 'vue';

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
  init(state, { pagination, microblog }) {
    if (pagination) {
      state = Object.assign(state, pagination);
    }

    if (microblog) {
      state.data.push(microblog);
    }
  },

  update(state, microblog: Microblog) {
    const index = state.data.findIndex(item => item.id === microblog.id);

    index > -1 ? Vue.set(state.data, index, microblog) : state.data.unshift(microblog);
  },

  updateComment(state, microblog: Microblog) {
    const parentIndex = state.data.findIndex(item => item.id === microblog.parent_id);
    const index = state.data[parentIndex].findIndex(item => item.id === microblog.id);

    index > -1 ? state.data[parentIndex].comments[index] = microblog : state.data[parentIndex].comments.push(microblog);
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
      commit('updateVotes', { microblog, votes: result.data });
    });
  },

  delete({ commit }, microblog) {
    axios.post(`/Mikroblogi/Delete/${microblog.id}`).then(result => {

    });
  },

  save({ commit }, microblog: Microblog) {
    axios.post(`/Mikroblogi/Edit/${microblog.id || ''}`, microblog).then(result => commit('update', result.data));
  },

  saveComment({ commit }, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Comment/${microblog.id || ''}`, microblog).then(result => commit('updateComment', result.data));
  },

  loadComments({ commit }, microblog: Microblog) {
    axios.get(`/Mikroblogi/Comment/Show/${microblog.id}`).then(result => {
      commit('setComments', { microblog, comments: result.data });
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

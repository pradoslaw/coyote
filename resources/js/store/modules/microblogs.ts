import axios from "axios";
import { Microblog, Paginator, Media } from "../../types/models";
import Vue from 'vue';

const state = {
  data: [],
  links: null,
  meta: null
};

const getters = {
  // @ts-ignore
  microblogs: state => Object.values(state.data).sort((a, b) => a.id > b.id ? -1 : 1),
  currentPage: state => state.meta.current_page,
  totalPages: state => state.meta.last_page
}

Array.prototype.keyBy = function (key: string) {
  return this.reduce((data, item) => {
    data[item[key]] = item;

    return data;
  }, {});
};

function merge(old, cur) {
  let { text, html } = cur; // update only text and html version

  return !old ? cur : {...old, ...{text, html}}
}

const mutations = {
  init(state, { pagination, microblog }) {
    if (pagination) {
      pagination.data = pagination.data
        .map(microblog => (microblog.comments = microblog.comments.keyBy('id'), microblog))
        .keyBy('id');

      state = Object.assign(state, pagination);
    }

    if (microblog) {
      microblog.comments = microblog.comments.keyBy('id');

      state.data = {[microblog.id]: microblog};
    }
  },

  update(state, microblog: Microblog) {
    Vue.set(state.data, microblog.id!, merge(state.data[microblog.id!], microblog))
  },

  delete(state, microblog: Microblog) {
    Vue.delete(state.data, microblog.id!);
  },

  deleteComment(state, microblog: Microblog) {
    Vue.delete(state.data[microblog.parent_id!].comments, microblog.id!);

    state.data[microblog.parent_id!].comments_count -= 1;
  },

  updateComment(state, microblog: Microblog) {
    const comments = state.data[microblog.parent_id!].comments;

    Vue.set(comments, microblog.id!, merge(comments[microblog.id!], microblog));
  },

  addEmptyImage(state, microblog: Microblog) {
    microblog.media.push({thumbnail: '', url: '', name: ''});
  },

  addImage(state, { microblog, media }) {
    microblog.media.push(media);
  },

  deleteImage(state, { microblog, media }) {
    microblog.media.splice(microblog.media.findIndex(item => item.name === media), 1);
  },

  subscribe(state, microblog: Microblog) {
    microblog.is_subscribed = ! microblog.is_subscribed;
  },

  vote(state, microblog: Microblog) {
    if (microblog.is_voted) {
      microblog.is_voted = false;
      microblog.votes -= 1;
    }
    else {
      microblog.is_voted = true;
      microblog.votes += 1;
    }
  },

  setVoters(state, { microblog, voters }) {
    // voters field does not exist in object, that's why it's not reactive. we must use set() method
    Vue.set(state.data, microblog.id!, {...microblog, voters});
  },

  setComments(state, { microblog, comments }) {
    microblog.comments = comments.keyBy('id');
    microblog.comments_count = comments.length;
  }
};

const actions = {
  subscribe({ commit }, microblog: Microblog) {
    commit('subscribe', microblog);

    axios.post(`/Mikroblogi/Subscribe/${microblog.id}`);
  },

  vote({ commit }, microblog: Microblog) {
    commit('vote', microblog);

    return axios.post(`/Mikroblogi/Vote/${microblog.id}`).catch(() => commit('vote', microblog));
  },

  delete({ commit }, microblog: Microblog) {
    return axios.delete(`/Mikroblogi/Delete/${microblog.id}`).then(() => commit('delete', microblog));
  },

  deleteComment({ commit }, microblog: Microblog) {
    return axios.delete(`/Mikroblogi/Comment/Delete/${microblog.id}`).then(() => commit('deleteComment', microblog));
  },

  save({ commit }, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Edit/${microblog.id || ''}`, microblog).then(result => commit('update', result.data));
  },

  saveComment({ state, commit }, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Comment/${microblog.id || ''}`, microblog).then(result => {
      commit('updateComment', result.data.data);

      if (result.data.is_subscribed) {
        state.data[result.data.data.parent_id].is_subscribed = true;
      }
    });
  },

  loadComments({ commit }, microblog: Microblog) {
    axios.get(`/Mikroblogi/Comment/Show/${microblog.id}`).then(result => {
      commit('setComments', { microblog, comments: result.data });
    })
  },

  loadVoters({ commit }, microblog: Microblog) {
    axios.get(`/Mikroblogi/Vote/${microblog.id}`).then(result => commit('setVoters', { microblog, voters: result.data }));
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

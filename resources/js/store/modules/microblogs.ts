import axios from "axios";
import { Microblog, Paginator, Tag } from "@/types/models";
import Vue from 'vue';

type ParentChild = { parent: Microblog, comment: Microblog };

const state: Paginator = {
  current_page: 0,
  data: [],
  from: 0,
  last_page: 0,
  path: "",
  per_page: 0,
  to: 0,
  total: 0
};

const getters = {
  microblogs: (state, getters, rootState) => {
    let sorted = Object.values(state.data).sort((a, b) => (b as Microblog).id! - (a as Microblog).id!);
    const sponsoredIndex = sorted.findIndex(microblog => (microblog as Microblog).is_sponsored);

    if (!rootState.user.is_sponsor && sponsoredIndex > -1) {
      const sponsored = sorted[sponsoredIndex];
      const random = (min: number, max: number) => Math.floor(Math.random() * (max - min) + min);
      const randomIndex = random(1, 4);

      if (sponsoredIndex > randomIndex) {
        sorted.splice(sponsoredIndex, 1);
        sorted.splice(randomIndex, 0, sponsored);
      }
    }

    return sorted;
  },

  exists: state => (id: number) => id in state.data,
  currentPage: state => state.current_page,
  totalPages: state => state.last_page
}

const mutations = {
  init(state, pagination: Paginator) {
    state = Object.assign(state, pagination);
  },

  add(state, microblog: Microblog) {
    Vue.set(state.data, microblog.id!, microblog);
  },

  update(state, microblog: Microblog) {
    let { text, html, assets } = microblog; // update only text and html version

    Vue.set(state.data, microblog.id!, {...state.data[microblog.id!], ...{ text, html, assets }})
  },

  delete(state, microblog: Microblog) {
    Vue.delete(state.data, microblog.id!);
  },

  restore(state, microblog: Microblog) {
    microblog.deleted_at = null;
  },

  addComment(state, { parent, comment }: ParentChild) {
    if (Array.isArray(parent.comments)) {
      Vue.set(parent, "comments", {});
    }

    Vue.set(parent.comments, comment.id!, comment);

    parent.comments_count! += 1;
  },

  updateComment(state, { parent, comment }: ParentChild) {
    let { text, html } = comment; // update only text and html version

    Vue.set(parent.comments, comment.id!, {...parent.comments[comment.id!], ...{text, html}});
  },

  deleteComment(state, comment: Microblog) {
    Vue.delete(state.data[comment.parent_id!].comments, comment.id!);

    state.data[comment.parent_id!].comments_count -= 1;
  },

  restoreComment(state, comment: Microblog) {
    comment.deleted_at = null;

    state.data[comment.parent_id!].comments_count += 1;
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

  setComments(state, { microblog, comments }) {
    microblog.comments = comments;
    microblog.comments_count = Object.keys(comments).length;
  },

  UPDATE_VOTERS(state, { microblog, users, includesLoggedUser }: { microblog: Microblog, users: string[], includesLoggedUser: boolean }) {
    Vue.set(microblog, 'voters', users);

    microblog.votes = users.length;
    microblog.is_voted = includesLoggedUser;
  },

  toggleTag(state, { microblog, tag }: { microblog: Microblog, tag: Tag }) {
    const index = microblog.tags!.findIndex(item => item.name === tag.name);

    index > -1 ? microblog.tags!.splice(index, 1) : microblog.tags!.push(tag);
  },

  toggleSponsored(state, microblog: Microblog) {
    microblog.is_sponsored = !microblog.is_sponsored;
  },

  toggleEdit(state, microblog: Microblog) {
    // we must use set() because is_editing can be undefined
    Vue.set(microblog, 'is_editing', !microblog.is_editing);
  },

  toggleSubscribed(state, microblog: Microblog) {
    microblog.is_subscribed = !microblog.is_subscribed;
  }
};

const actions = {
  subscribe({ commit }, microblog: Microblog) {
    commit('toggleSubscribed', microblog);

    axios.post(`/Mikroblogi/Subscribe/${microblog.id}`);
  },

  vote({ commit }, microblog: Microblog) {
    commit('vote', microblog);

    return axios.post(`/Mikroblogi/Vote/${microblog.id}`)
      .then(result => commit('setVoters', { microblog, response: result.data, isVoted: true }))
      .catch(() => commit('vote', microblog));
  },

  delete({ commit }, microblog: Microblog) {
    return axios.delete(`/Mikroblogi/Delete/${microblog.id}`).then(() => commit('delete', microblog));
  },

  restore({ commit }, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Restore/${microblog.id}`).then(() => commit('restore', microblog));
  },

  deleteComment({ commit }, microblog: Microblog) {
    return axios.delete(`/Mikroblogi/Comment/Delete/${microblog.id}`).then(() => commit('deleteComment', microblog));
  },

  restoreComment({ commit }, comment: Microblog) {
    return axios.post(`/Mikroblogi/Restore/${comment.id}`).then(() => commit('restoreComment', comment));
  },

  save({ commit, getters }, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Edit/${microblog.id || ''}`, microblog).then(result => {
      commit(getters.exists(result.data.id) ? 'update' : 'add', result.data);

      return result;
    });
  },

  saveComment({ state, commit, getters }, comment: Microblog) {
    return axios.post(`/Mikroblogi/Comment/${comment.id || ''}`, comment).then(response => {
      const comment = response.data.data;
      const parent = state.data[comment.parent_id!];

      if (parent.comments[comment.id!]) {
        commit('updateComment', { parent, comment });
      }
      else {
        commit('addComment', { parent, comment });

        if (response.data.is_subscribed && !parent.is_subscribed) {
          commit('toggleSubscribed', parent);
        }
      }

      return response;
    });
  },

  loadComments({ commit }, microblog: Microblog) {
    return axios.get(`/Mikroblogi/Comment/Show/${microblog.id}`).then(response => commit('setComments', { microblog, comments: response.data }));
  },

  loadVoters({ commit, dispatch }, microblog: Microblog) {
    return axios.get(`/Mikroblogi/Voters/${microblog.id}`).then(response => {
      dispatch('updateVoters', { microblog, users: response.data.users });
    });
  },

  updateVoters({ commit, rootState }, { microblog, users }: { microblog: Microblog, users: string[] }) {
    commit('UPDATE_VOTERS', { microblog, users, includesLoggedUser: users.includes(rootState.user.user.name) });
  },

  toggleSponsored({ commit }, microblog: Microblog) {
    return axios.post(`/Mikroblogi/Sponsored/${microblog.id}`).then(() => commit('toggleSponsored', microblog));
  },

  hit({ commit }, microblog: Microblog) {
    return navigator.sendBeacon(`/Mikroblogi/Hit/${microblog.id}`);
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

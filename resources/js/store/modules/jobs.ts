import Vue from 'vue';
import {Job, Firm, JobFeature, Tag} from '../../types/models';
import axios from "axios";

const state = {
  data: [],
  links: [],
  meta: [],
  form: {},
  subscriptions: [],
  comments: {}
}

const getters = {
  isSubscribed: state => (job: Job) => state.subscriptions.findIndex(item => item.id === job.id) > -1
}

const mutations = {
  INIT_FORM(state, job: Job) {
    state.form = job;
  },

  SET_FIRM(state, firm: Firm) {
    state.form.firm = firm;
  },

  ADD_LOCATION(state) {
    state.form.locations.push({});
  },

  REMOVE_LOCATION(state, location) {
    state.form.locations.splice(state.form.locations.indexOf(location), 1);
  },

  SET_LOCATION(state, { index, location }) {
    Vue.set(state.form.locations, index, location);
  },

  ADD_TAG(state, name) {
    state.form.tags.push({ name: name, priority: 2 });
  },

  REMOVE_TAG(state, tag: Tag) {
    state.form.tags.splice(state.form.tags.findIndex(el => el.name === tag.name), 1);
  },

  TOGGLE_FEATURE(state, feature: JobFeature) {
    feature.checked = !feature.checked;
  },

  TOGGLE_BENEFIT(state, benefit) {
    let index = state.form.firm.benefits.indexOf(benefit);

    if (index === -1) {
      state.form.firm.benefits.push(benefit);
    } else {
      state.form.firm.benefits.splice(index, 1);
    }
  },

  ADD_BENEFIT(state, benefit) {
    state.form.firm.benefits.push(benefit);
  },

  REMOVE_BENEFIT(state, benefit) {
    state.form.firm.benefits.splice(state.form.firm.benefits.indexOf(benefit), 1);
  },

  SUBSCRIBE(state, job: Job) {
    state.subscriptions.push(job);
  },

  UNSUBSCRIBE(state, job: Job) {
    const index = state.subscriptions.findIndex(item => item.id === job.id);

    state.subscriptions.splice(index, 1);
  },

  SET_COMMENTS(state, comments) {
    state.comments = comments;
  },

  UPDATE_COMMENT(state, comment) {
    if (Array.isArray(state.comments)) {
      Vue.set(state, "comments", {});
    }

    if (comment.parent_id) {
      const parent = state.comments[comment.parent_id];

      if (Array.isArray(parent.children)) {
        Vue.set(parent, "children", {});
      }

      Vue.set(parent.children, comment.id, comment);
    } else {
      Vue.set(state.comments, comment.id, comment);
    }
  },

  DELETE_COMMENT(state, comment) {
    if (comment.parent_id) {
      let parent = state.comments[comment.parent_id];

      Vue.delete(parent.children, comment.id);
    } else {
      Vue.delete(state.comments, comment.id);
    }
  },
}

const actions = {
  save({ state }) {
    return axios.post(`/Praca/Submit/${state.form.id ?? ''}`, state.form);
  },

  subscribe({ commit, getters }, job: Job) {
    getters.isSubscribed(job) ? commit('UNSUBSCRIBE', job) : commit('SUBSCRIBE', job);

    return axios.post(`/Praca/Subscribe/${job.id}`);
  },

  saveComment({ commit }, comment) {
    return axios.post(`/Praca/Comment/${comment.id || ''}`, comment)
      .then(result => {
        commit('UPDATE_COMMENT', result.data);

        return result;
      });
  },

  deleteComment({ commit }, comment) {
    return axios.delete(`/Praca/Comment/${comment.id}`).then(() => commit('DELETE_COMMENT', comment));
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};


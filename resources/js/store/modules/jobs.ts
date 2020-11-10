import Vue from 'vue';
import {Job, JobFeature} from '../../types/models';
import axios from "axios";

const state = {
  data: [],
  links: [],
  meta: [],
  form: {}
}

const mutations = {
  INIT_FORM(state, job: Job) {
    state.form = job;
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
    state.form.tags.push({ name: name, priority: 1 });
  },

  REMOVE_TAG(state, name) {
    state.form.tags.splice(state.form.tags.findIndex(el => el.name === name), 1);
  },

  TOGGLE_FEATURE(state, feature: JobFeature) {
    feature.checked = !feature.checked;
  },

}

const actions = {
  save({ commit, state }) {
    return axios.post(`/Praca/Submit/${state.form.id}`, state.form);
  }
}


export default {
  namespaced: true,
  state,
  mutations,
  actions
};


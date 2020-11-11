import Vue from 'vue';
import { Job, Firm, JobFeature } from '../../types/models';
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
    state.form.tags.push({ name: name, priority: 1 });
  },

  REMOVE_TAG(state, name) {
    state.form.tags.splice(state.form.tags.findIndex(el => el.name === name), 1);
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

  ADD_LOGO(state, logo) {
    state.form.firm.logo = logo;
  },

  REMOVE_LOGO(state) {
    state.form.firm.logo = null;
  },

  ADD_PHOTO(state, photo) {
    state.form.firm.gallery.splice(state.form.firm.gallery.length - 1, 0, photo);
  },

  REMOVE_PHOTO(state, url) {
    const gallery = state.form.firm.gallery;

    gallery.splice(gallery.findIndex(photo => photo === url), 1);
  },
}

const actions = {
  save({ state }) {
    return axios.post(`/Praca/Submit/${state.form.id ?? ''}`, state.form);
  }
}


export default {
  namespaced: true,
  state,
  mutations,
  actions
};


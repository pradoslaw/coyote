import axios from "axios";
import {Firm, Job, JobFeature, Tag} from '../../types/models';

const state = {
  data: [],
  links: [],
  meta: [],
  form: {},
  subscriptions: [],
};

const getters = {
  isSubscribed: state => (job: Job) => state.subscriptions.findIndex(item => item.id === job.id) > -1,
};

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

  SET_LOCATION(state, {index, location}) {
    state.form.locations[index] = location;
  },

  ADD_TAG(state, name) {
    state.form.tags.push({name: name, priority: 2});
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
};

const actions = {
  save({state}) {
    return axios.post(`/Praca/Submit/${state.form.id ?? ''}`, state.form);
  },

  subscribe({commit, getters}, job: Job) {
    getters.isSubscribed(job) ? commit('UNSUBSCRIBE', job) : commit('SUBSCRIBE', job);

    return axios.post(`/Praca/Subscribe/${job.id}`);
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};


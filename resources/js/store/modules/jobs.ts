import Vue from 'vue';
import {Job, JobFeature} from '../../types/models';
import axios from "axios";

const state = {
  data: [],
  links: [],
  meta: []
}

const mutations = {
  ADD(state, job: Job) {
    state.data.push(job);
  },

  ADD_LOCATION(state, job: Job) {
    job.locations.push({});
  },

  REMOVE_LOCATION(state, { job, location }) {
    job.locations.splice(job.locations.indexOf(location), 1);
  },

  SET_LABEL(state, { job, index, label }) {
    Vue.set(job.locations, index, {...job.locations[index], ...{ label }});
  },

  ADD_TAG(state, { job, name }) {
    job.tags.push({ name: name, priority: 1 });
    // // fetch only tag name
    // let pluck = this.job.tags.map(item => item.name);
    //
    // // request suggestions
    // axios.get(this.suggestion_url, {params: {t: pluck}})
    //   .then(response => {
    //     this.suggestions = response.data;
    //   });
  },

  REMOVE_TAG(state, { job, name }) {
    job.tags.splice(job.tags.findIndex(el => el.name === name), 1);
  },

  TOGGLE_FEATURE(state, feature: JobFeature) {
    feature.checked = !feature.checked;
  },

}

const actions = {
  save({ commit }, job: Job) {

  }
}


export default {
  namespaced: true,
  state,
  mutations,
  actions
};


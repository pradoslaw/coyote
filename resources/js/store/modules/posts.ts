import axios from "axios";
import { Post, Paginator, Media } from "../../types/models";
import Vue from 'vue';


const state = {
  data: [],
  links: null,
  meta: null
};

const getters = {
  posts: state => state.data
}

const mutations = {
  init(state, { pagination }) {
    console.log(pagination);

    // pagination.data = pagination.data
      // .map(post => (post.comments = post.comments.keyBy('id'), post))
      // .keyBy('id');

    state = Object.assign(state, pagination);
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  // actions
};


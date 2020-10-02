import axios from "axios";

const state = {
  categories: []
};

const getters = {
  findIndex: (state) => (order, section) => {
    return state.categories.findIndex(value => value.order === order && value.section === section);
  },
  forum: state => state.categories[0]
};

const pluck = (category) => {
  return (({ id, is_hidden, order }) => ({ id, is_hidden, order }))(category);
};

const setup = categories => axios.post('/Forum/Setup', categories.map(category => pluck(category)));

const mutations = {
  init(state, categories) {
    state.categories = categories;
  },

  toggle(state, category) {
    category.is_hidden = ! category.is_hidden;
  },

  mark(state, category) {
    category.is_read = true;

    if (category.children) {
      category.children.forEach(child => child.is_read = true);
    }
  }
};

const actions = {
  toggle({ commit }, category) {
    commit('toggle', category);

    axios.post('/Forum/Setup', [ pluck(category) ]);
  },

  collapse({ commit }, category) {
    axios.post(`/Forum/${category.slug}/Collapse`);
  },

  mark({ commit }, category) {
    commit('mark', category);

    axios.post(`/Forum/${category.slug}/Mark`);
  },

  markAll({ state, commit }) {
    axios.post('/Forum/Mark');

    state.categories.forEach(category => commit('mark', category));
  },

  up({ getters, state }, category) {
    let aboveIndex = getters.findIndex(category.order - 1, category.section);

    if (aboveIndex > -1) {
      category.order -= 1;
      state.categories[aboveIndex].order += 1;

      setup([category, state.categories[aboveIndex]]);
    }
  },

  down({ getters, state }, category) {
    let beyondIndex = getters.findIndex(category.order + 1, category.section);

    if (beyondIndex > -1) {
      category.order += 1;
      state.categories[beyondIndex].order -= 1;

      setup([category, state.categories[beyondIndex]]);
    }
  }
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
};

const state = {

}

const mutations = {
  INIT(state, comments) {
    state = Object.assign(state, comments);
  }
}

const actions = {

}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

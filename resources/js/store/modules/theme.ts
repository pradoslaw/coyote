const state = {
  darkTheme: window.document.body.classList.contains('theme-dark'),
};

const mutations = {
  CHANGE_THEME(state, dark: boolean) {
    state.darkTheme = dark;
  },
};

export default {
  namespaced: true,
  state,
  mutations,
};

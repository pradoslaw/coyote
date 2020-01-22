const state = window._config;

const getters = {
  isAuthorized: state => state.id !== null,
  dateFormat: state => (defaultFormat = null) => state.date_format ? state.date_format : defaultFormat
};

export default {
  namespaced: true,
  state,
  getters
};

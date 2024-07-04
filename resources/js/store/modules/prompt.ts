import axios from "axios";

const CancelToken = axios.CancelToken;

const state = {
  source: CancelToken.source()
}

const mutations = {
  cancel(state) {
    state.source.cancel();
    state.source = CancelToken.source();
  }
}

const actions = {
  request ({ state, commit }, { source, value }) {
    commit('cancel');

    return axios.get<any>(source, {cancelToken: state.source.token, params: {q: value}, errorHandle: false})
      .then(response => {
        let items = response.data;

        if (items.length === 1 && items[0].name.toLowerCase() === value.toLowerCase()) {
          items = [];
        }

        return items;
      })
      .catch(err => axios.isCancel(err) ? console.log('Request canceled') : console.error(err));
  }
};

export default {
  namespaced: true,
  mutations,
  state,
  actions
};

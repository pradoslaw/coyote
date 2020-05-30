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
  request ({ state, commit }, value: string) {
    commit('cancel');

    if (!value.trim().length) {
      return Promise.resolve([]);
    }

    return axios.get('/User/Prompt', {cancelToken: state.source.token, params: {q: value}})
      .then(response => {
        let items = response.data.data;

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

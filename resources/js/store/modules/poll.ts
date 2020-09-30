import { Poll, PollItem } from '../../types/models';
import axios from 'axios';

const DEFAULTS = {
  max_items: 1,
  length: 0,
  items: [
    {text: ''},
    {text: ''}
  ]
};

const state = {
  poll: DEFAULTS
}

const mutations = {
  init(state, poll: Poll) {
    if (poll) {
      state.poll = poll;
    }
  },

  removeItem(state, item: PollItem) {
    if (state.poll.items.length > 2) {
      state.poll.items.splice(state.poll.items.indexOf(item), 1);
    }
  },

  addItem(state) {
    state.poll.items.push({});
  },

  resetDefaults(state) {
    state.poll = DEFAULTS;
  }
}

const actions = {
  vote({ state, commit }, options: number[]) {
    return axios.post(`/Forum/Poll/${state.poll.id}`, { items: options }).then(result => commit('init', result.data));
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

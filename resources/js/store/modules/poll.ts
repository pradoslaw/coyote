import { Poll, PollItem } from '../../types/models';

const state = {
  poll: {
    title: '',
    max_items: 1,
    length: 0,
    items: [
      {text: ''},
      {text: ''}
    ]
  }
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
  }
}

const actions = {
  vote() {
    //
  }
}

export default {
  namespaced: true,
  state,
  mutations,
  actions
};

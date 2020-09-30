import { Poll, PollItem } from '../../types/models';

const state = {
  poll: {
    items: [{text: ''}]
  }
}

const mutations = {
  init(state, poll: Poll) {
    if (poll) {
      state.poll = poll;
    }
  },

  removeItem(state, item: PollItem) {
    if (state.poll.items.length > 1) {
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

const state = {
  comments: []
};

function _findIndex(state, id) {
  return state.comments.findIndex(comment => comment.id === id);
}

// mutations
const mutations = {
  init(state, comments) {
    state.comments = comments;
  },

  remove(state, payload) {
    if (payload.parent_id) {
      let parent = state.comments[_findIndex(state, payload.parent_id)];

      let index = parent.children.findIndex(comment => comment.id === payload.id);
      parent.children.splice(index, 1);
    } else {
      state.comments.splice(_findIndex(state, payload.id), 1);
    }
  },

  add(state, payload) {
    state.comments.unshift(payload);
  },

  update(state, payload) {
    if (payload.parent_id) {
      let parentIndex = _findIndex(state, payload.parent_id);
      let parent = state.comments[parentIndex];

      let index = parent.children.findIndex(comment => comment.id === payload.id);

      Vue.set(state.comments[parentIndex].children, index, payload);
    } else {
      Vue.set(state.comments, _findIndex(state, payload.id), payload);
    }
  },

  reply(state, payload) {
    state.comments[_findIndex(state, payload.parent_id)].children.push(payload);
  },
};

export default {
  namespaced: true,
  state,
  mutations
};

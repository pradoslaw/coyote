const state = {
    messages: []
};

const mutations = {
    init (state, messages) {
        state.messages = messages;
    },

    add (state, payload) {
        state.messages.push(payload);
    },

    pop (state, index) {
        state.messages.splice(index, 1);
    }
};


export default {
    namespaced: true,
    state,
    mutations,
};

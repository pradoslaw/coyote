const state = {
    messages: []
};

// const getters = {
//     exists: state => (payload) => state.subscribed.findIndex(item => item.id === payload.id)
// };

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
    // getters,
    state,
    mutations,
};

const state = {
    subscribed: []
};

const getters = {
    exists: state => (payload) => state.subscribed.findIndex(item => item.id === payload.id)
};

const mutations = {
    init (state, subscriptions) {
        state.subscribed = subscriptions;
    },

    toggle (state, payload) {
        let index = state.getters.exists(payload);

        if (index > -1) {
            state.subscribed.splice(index, 1);
        }
        else {
            state.subscribed.push(payload);
        }
    },
};


export default {
    namespaced: true,
    getters,
    state,
    mutations
};

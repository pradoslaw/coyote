import axios from 'axios';

const state = {
    subscribed: []
};

const getters = {
    exists: state => (payload) => state.subscribed.findIndex(item => item.id === payload.id)
};

const mutations = {
    push (state, payload) {
        state.subscribed.push(payload);
    },

    pop (state, index) {
        state.subscribed.splice(index, 1);
    }
};

const actions = {
    toggle ({ commit, getters }, payload) {
        let index = getters.exists(payload);

        if (index > -1) {
            commit('pop', index);
        }
        else {
            commit('push', payload);
        }

        return axios.post(`/Praca/Subscribe/${payload.id}`);
    }
};

export default {
    namespaced: true,
    getters,
    state,
    mutations,
    actions
};

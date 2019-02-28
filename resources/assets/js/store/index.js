import Vue from 'vue';
import Vuex from 'vuex';
import comments from './modules/comments';
import subscriptions from './modules/subscriptions';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        comments,
        subscriptions
    }
});

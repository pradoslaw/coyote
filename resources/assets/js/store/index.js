import Vue from 'vue';
import Vuex from 'vuex';
import comments from './modules/comments';
import subscriptions from './modules/subscriptions';
import messages from './modules/messages';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        comments,
        subscriptions,
        messages
    }
});

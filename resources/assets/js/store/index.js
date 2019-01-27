import Vue from 'vue';
import Vuex from 'vuex';
import comments from './modules/comments';

Vue.use(Vuex);

export default new Vuex.Store({
    modules: {
        comments
    }
});

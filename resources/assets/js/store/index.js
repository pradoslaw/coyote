import Vue from 'vue';
import Vuex from 'vuex';
import comments from './modules/comments';
import subscriptions from './modules/subscriptions';
import messages from './modules/messages';
import inbox from './modules/inbox';
import notifications from './modules/notifications';
import forums from './modules/forums';
import user from './modules/user';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    comments,
    subscriptions,
    messages,
    inbox,
    notifications,
    forums,
    user
  }
});

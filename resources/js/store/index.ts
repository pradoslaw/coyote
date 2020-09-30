import Vue from 'vue';
import Vuex from 'vuex';
import comments from './modules/comments';
import subscriptions from './modules/subscriptions';
import messages from './modules/messages';
import inbox from './modules/inbox';
import notifications from './modules/notifications';
import forums from './modules/forums';
import topics from './modules/topics';
import posts from './modules/posts';
import user from './modules/user';
import microblogs from './modules/microblogs';
import prompt from './modules/prompt';
import poll from './modules/poll';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    comments,
    subscriptions,
    messages,
    inbox,
    notifications,
    forums,
    topics,
    posts,
    user,
    microblogs,
    prompt,
    poll
  }
});

import Vue from 'vue';
import Vuex from 'vuex';
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
import flags from './modules/flags';
import jobs from './modules/jobs';
import questions from './modules/questions';

Vue.use(Vuex);

export default new Vuex.Store({
  modules: {
    messages,
    inbox,
    notifications,
    forums,
    topics,
    posts,
    user,
    microblogs,
    prompt,
    poll,
    flags,
    jobs,
    questions
  }
});

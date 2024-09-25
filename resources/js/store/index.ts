import {createStore} from 'vuex';
import comments from './modules/comments';
import flags from './modules/flags';
import forums from './modules/forums';
import guides from './modules/guides';
import inbox from './modules/inbox';
import jobs from './modules/jobs';
import messages from './modules/messages';
import microblogs from './modules/microblogs';
import notifications from './modules/notifications';
import poll from './modules/poll';
import posts from './modules/posts';
import prompt from './modules/prompt';
import theme from './modules/theme';
import topics from './modules/topics';
import user from './modules/user';

export default createStore({
  modules: {
    messages,
    inbox,
    theme,
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
    guides,
    comments,
  },
});

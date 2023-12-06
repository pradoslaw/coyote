import Vue from "vue";
import VueTimeago from '../plugins/timeago.js';
import VueNotifications from 'vue-notification';
import VuePaste from "../plugins/paste.js";
import VueModals from "../plugins/modals";
import VueAutosave from "../plugins/autosave";
import VueForum from './forum/homepage';
import VuePosts from './forum/posts';
import VueLog from './forum/log';
import * as Models from "../types/models";
import {default as axiosErrorHandler} from '../libs/axios-error-handler.js';
import './forum/sidebar';
import './forum/tags';

Vue.use(VueTimeago);
Vue.use(VueNotifications, {componentName: 'vue-notifications'});
Vue.use(VueModals);
Vue.use(VuePaste, {url: '/assets'});
Vue.use(VueAutosave);

axiosErrorHandler((message: string) => Vue.notify({type: 'error', text: message}));

declare global {
  interface Window {
    pagination: Models.Paginator;
    topic: Models.Topic;
    post: Models.Post;
    forum: Models.Forum;
    poll: Models.Poll;
    tags: Models.Tag[];
    showStickyCheckbox: boolean;
    reasons: string[];
    allForums: Models.Forum[];
    showCategoryName: boolean;
    groupStickyTopics: boolean;
    topics: Models.Paginator;
    popularTags: string[];
    logs: Models.PostLog[];
    topicLink: string;
  }
}

const boot = {'js-forum': VueForum, 'js-post': VuePosts, 'js-log': VueLog};

for (const el in boot) {
  if (document.getElementById(el)) {
    new boot[el]().$mount('#' + el);
  }
}

document.getElementById('js-forum-list')
  ?.addEventListener('change', event => window.location.href = `/Forum/${(event.target as HTMLSelectElement).value}`);

document.getElementById('js-reload')
  ?.addEventListener('click', () => window.location.reload());

document.getElementById('js-per-page')
  ?.addEventListener('change', event => {
    const perPage = (event.target as HTMLSelectElement).value;
    const url = (event.target as HTMLSelectElement).dataset.url;
    window.location.href = `${url}?perPage=${perPage}`;
  });

document.getElementById('btn-toggle-sidebar')
  ?.addEventListener('click', function () {
    document.getElementById('sidebar')!.classList.toggle('d-block');
    return false;
  });

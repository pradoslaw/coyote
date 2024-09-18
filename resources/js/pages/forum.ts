import Vue from "vue";
import VueNotifications from 'vue-notification';

import {default as axiosErrorHandler} from '../libs/axios-error-handler.js';
import VueAutosave from "../plugins/autosave";
import VuePaste from "../plugins/paste.js";
import * as Models from "../types/models";

import VueForum from './forum/homepage';
import VueLog from './forum/log';
import VuePosts from './forum/posts';
import './forum/sidebar';
import './forum/tags';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});
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
    emojis: Models.Emojis;
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
    new (Vue.extend(boot[el]))().$mount('#' + el);
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

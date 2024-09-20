import Vue from "vue";
import VueNotifications from 'vue-notification';

import {default as axiosErrorHandler} from '../libs/axios-error-handler.js';
import * as Models from "../types/models";

import VueForum from './forum/homepage';
import VueLog from './forum/log';
import VuePosts from './forum/posts';
import VueSidebar from './forum/sidebar';
import VueTags from './forum/tags';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

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

createVueApp(VueForum, '#js-forum');
createVueApp(VuePosts, '#js-post');
createVueApp(VueLog, '#js-log');
createVueApp(VueSidebar, '#js-sidebar');
createVueApp(VueTags, '#js-tags');

function createVueApp(component: object, selector: string): void {
  if (document.querySelector(selector)) {
    new (Vue.extend(component))().$mount(selector);
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

import axios from "axios";
import {Tag, Topic} from '../../types/models';

export type PostOrdering = 'orderByCreationDate' | 'orderByLikes';

const state = {
  topics: [],
  reasons: [],
  treePostOrdering: 'orderByCreationDate',
};

const getters = {
  topic: state => state.topics[0],
  is_mode_tree: state => state.topics[0].discuss_mode === 'tree',
  is_mode_linear: state => state.topics[0].discuss_mode === 'linear',
  treeTopicPostOrdering(state): PostOrdering {
    return state.treePostOrdering;
  },
};

const mutations = {
  init(state, topics: Topic[]) {
    state.topics = topics;
  },

  setReasons(state, reasons) {
    state.reasons = reasons;
  },

  mark(state, topic: Topic) {
    topic.is_read = true;
  },

  markAll(state) {
    state.topics.forEach(topic => topic.is_read = true);
  },

  subscribe(state, topic: Topic) {
    if (topic.is_subscribed) {
      topic.subscribers!--;
    } else {
      topic.subscribers!++;
    }

    topic.is_subscribed = !topic.is_subscribed;
  },

  lock(state, topic: Topic) {
    topic.is_locked = !topic.is_locked;
  },

  toggleTag(state, {topic, tag}: { topic: Topic, tag: Tag }) {
    const index = topic.tags!.findIndex(item => item.name === tag.name);

    index > -1 ? topic.tags!.splice(index, 1) : topic.tags!.push(tag);
  },
  postOrdering(state, ordering: PostOrdering): void {
    state.treePostOrdering = ordering;
  },
};

const actions = {
  mark({commit}, topic) {
    commit('mark', topic);

    axios.post(`/Forum/Topic/Mark/${topic.id}`);
  },

  markAll({state, commit}) {
    commit('markAll');

    axios.post(`/Forum/${state.topics[0].forum.slug}/Mark`);
  },

  subscribe({commit}, topic) {
    commit('subscribe', topic);

    axios.post(`/Forum/Topic/Subscribe/${topic.id}`);
  },

  lock({commit}, topic) {
    commit('lock', topic);

    axios.post(`/Forum/Topic/Lock/${topic.id}`);
  },

  move({commit}, {topic, forumId, reasonId}) {
    return axios.post(`/Forum/Topic/Move/${topic.id}`, {id: forumId, reason_id: reasonId});
  },

  changeTitle({commit}, {topic}) {
    return axios.post(`/Forum/Topic/Subject/${topic.id}`, {title: topic.title});
  },
};

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
};

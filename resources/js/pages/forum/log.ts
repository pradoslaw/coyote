import VuePostLog from "../../components/forum/post-log.vue";
import store from '../../store/index';

export default {
  delimiters: ['${', '}'],
  components: {'vue-log': VuePostLog},
  store,
  data: () => ({
    logs: window.logs,
    topicLink: window.topicLink,
  }),
  methods: {
    oldStr(logs, index) {
      if (index == logs.length - 1) {
        return null;
      }
      return logs[index + 1].text;
    },
  },
};

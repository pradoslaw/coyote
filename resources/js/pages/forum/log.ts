import Vue from "vue";
import VuePostLog from "@/components/forum/post-log.vue";
import store from '@/store';

export default Vue.extend({
  delimiters: ['${', '}'],
  components: { 'vue-log': VuePostLog },
  store,
  data: () => ({
    logs: window.logs
  }),
  methods: {
    oldStr(logs, index) {
      return index == logs.length -1 ? null : logs[index + 1].text;
    }
  }
});

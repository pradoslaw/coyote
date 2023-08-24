import Vue from "vue";
import VueGithubStar from "@/components/github-star.vue";

new Vue({
  el: '#navbar',
  components: {
    'vue-github-star': VueGithubStar,
  },
});

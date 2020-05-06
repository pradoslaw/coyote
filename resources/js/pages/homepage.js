import PerfectScrollbar from 'perfect-scrollbar';
import Vue from "vue";
import VueNotifications from "vue-notification";
import VueMicroblog from "../components/microblog/microblog";
import store from "../store";
import {mapGetters} from "vuex";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

new Vue({
  el: '#js-microblog',
  delimiters: ['${', '}'],
  components: { 'vue-microblog': VueMicroblog },
  store,
  created() {
    store.commit('microblogs/init', { pagination: { data: window.microblogs } });
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs']),


  }
});

$(function () {
    new PerfectScrollbar(document.getElementById('stream'));

    let tabs = {'forum': $('#forum-tabs').find('a'), 'reputation': $('#reputation-tabs').find('a')};

    tabs.forum.click(function() {
        let index = tabs.forum.index(this);

        $.post(__INITIAL_STATE.settings_url, {'homepage_mode': index});
    });

    tabs.reputation.click(function() {
        let index = tabs.reputation.index(this);

        $.post(__INITIAL_STATE.settings_url, {'homepage_reputation': index});
    });
});

import Vue from "vue";
import VueThumbnail from '../components/thumbnail';
import VueNotifications from 'vue-notification';
import axios from 'axios';
import store from '../store';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  el: '#js-user',
  delimiters: ['${', '}'],
  components: { 'vue-thumbnail': VueThumbnail },
  methods: {
    setPhoto(data) {
      store.commit('user/update', { photo: data.url });
    },

    deletePhoto() {
      axios.delete('/User/Photo/Delete');

      store.commit('user/update', { photo: null });
    }
  },
  computed: {
    url() {
      return store.state.user.photo;
    }
  }
});

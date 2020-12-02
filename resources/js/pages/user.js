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
      return store.state.user.user.photo;
    }
  }
});

new Vue({
  el: '#js-skills',
  delimiters: ['${', '}'],
  data() {
    return {
      skills: window.skills,
      rateLabels: window.rateLabels,
      selectedMark: null,
      clickedMark: null,
      skillName: null
    };
  },
  methods: {
    addSkill() {
      axios.post('/User/Skills', {name: this.skillName, rate: this.clickedMark}).then(() => {
        this.skills.push({
          name: this.skillName,
          rate: this.clickedMark
        });

        this.clickedMark = this.selectedMark = this.skillName = null;
      });
    },

    deleteSkill(id) {
      this.skills.splice(this.skills.findIndex(skill => skill.id === id), 1);

      axios.delete(`/User/Skills/${id}`);
    },

    setMark(mark) {
      this.clickedMark = mark;
    },

    selectMark(mark) {
      this.selectedMark = mark;
    },

    clearMarks() {
      this.selectedMark = null;
    }
  }
});

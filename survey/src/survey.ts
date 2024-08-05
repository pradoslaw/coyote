import Vue from "vue";
import SurveyEnroll from "./screen/enroll";

new Vue({
  el: '#survey',
  components: {'vue-survey-enroll': SurveyEnroll},
  template: '<vue-survey-enroll/>',
});
